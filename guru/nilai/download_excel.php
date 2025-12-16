<?php
require_once '../../config.php';
requireRole('guru');

$id_user = $_SESSION['user_id'];

// Ambil data semester aktif
$query_semester = "SELECT * FROM semester WHERE status = 'aktif' LIMIT 1";
$result_semester = mysqli_query($conn, $query_semester);
$semester_aktif = mysqli_fetch_assoc($result_semester);

// Ambil rombel dan mapel yang diajar guru
$query_mapel_guru = "SELECT DISTINCT r.id_rombel, r.nama_rombel, m.id_mapel, m.nama_mapel
                     FROM mapel_guru mg
                     INNER JOIN rombel r ON mg.id_rombel = r.id_rombel
                     INNER JOIN mata_pelajaran m ON mg.id_mapel = m.id_mapel
                     WHERE mg.id_user = $id_user";
$result_mapel_guru = mysqli_query($conn, $query_mapel_guru);

// Filter
$filter_rombel = isset($_GET['rombel']) ? cleanInput($_GET['rombel']) : '';
$filter_mapel = isset($_GET['mapel']) ? cleanInput($_GET['mapel']) : '';

// Proses download
if (isset($_GET['download']) && $filter_rombel && $filter_mapel && $semester_aktif) {
    // Ambil data rombel
    $query_rombel = "SELECT * FROM rombel WHERE id_rombel = $filter_rombel";
    $rombel = mysqli_fetch_assoc(mysqli_query($conn, $query_rombel));
    
    // Ambil data mata pelajaran
    $query_mapel = "SELECT * FROM mata_pelajaran WHERE id_mapel = $filter_mapel";
    $mapel = mysqli_fetch_assoc(mysqli_query($conn, $query_mapel));
    
    // Ambil data siswa
    $query_siswa = "SELECT s.* FROM siswa s 
                    WHERE s.id_rombel = '$filter_rombel' AND s.status = 'aktif'
                    ORDER BY s.nama_lengkap";
    $result_siswa = mysqli_query($conn, $query_siswa);
    
    // Set header untuk download Excel
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Nilai_{$mapel['nama_mapel']}_{$rombel['nama_rombel']}_{$semester_aktif['nama_semester']}.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    echo '<table border="1">';
    echo '<tr><td colspan="11" style="text-align:center;font-weight:bold;font-size:18px;">SMK MUHAMMADIYAH 8 PAKIS</td></tr>';
    echo '<tr><td colspan="11" style="text-align:center;font-weight:bold;font-size:16px;">DAFTAR NILAI SISWA</td></tr>';
    echo '<tr><td colspan="11"></td></tr>';
    
    // Metadata untuk upload (JANGAN DIHAPUS!)
    echo '<tr style="display:none;"><td>ID_ROMBEL</td><td>' . $filter_rombel . '</td><td>ID_MAPEL</td><td>' . $filter_mapel . '</td><td>ID_GURU</td><td>' . $id_user . '</td><td>ID_SEMESTER</td><td>' . $semester_aktif['id_semester'] . '</td></tr>';
    
    echo '<tr><td>Kelas/Rombel</td><td colspan="3">: ' . $rombel['nama_rombel'] . '</td><td>Semester</td><td colspan="3">: ' . $semester_aktif['nama_semester'] . '</td></tr>';
    echo '<tr><td>Mata Pelajaran</td><td colspan="3">: ' . $mapel['nama_mapel'] . '</td><td>KKM</td><td colspan="3">: ' . $mapel['kkm'] . '</td></tr>';
    echo '<tr><td>Guru</td><td colspan="3">: ' . $_SESSION['nama_lengkap'] . '</td><td>Tahun Ajaran</td><td colspan="3">: ' . $semester_aktif['tahun_ajaran'] . '</td></tr>';
    echo '<tr><td colspan="11"></td></tr>';
    
    // Header tabel
    echo '<tr style="background-color: #4472C4; color: white; font-weight: bold;">';
    echo '<td rowspan="2" style="text-align:center;">No</td>';
    echo '<td rowspan="2" style="text-align:center;">NIS</td>';
    echo '<td rowspan="2" style="text-align:center;">Nama Siswa</td>';
    echo '<td colspan="4" style="text-align:center;">Nilai Formatif</td>';
    echo '<td rowspan="2" style="text-align:center;">STS</td>';
    echo '<td rowspan="2" style="text-align:center;">SAS</td>';
    echo '<td rowspan="2" style="text-align:center;">Rata-rata</td>';
    echo '<td rowspan="2" style="text-align:center;">Deskripsi</td>';
    echo '</tr>';
    
    echo '<tr style="background-color: #4472C4; color: white; font-weight: bold;">';
    echo '<td style="text-align:center;">F1</td>';
    echo '<td style="text-align:center;">F2</td>';
    echo '<td style="text-align:center;">F3</td>';
    echo '<td style="text-align:center;">F4</td>';
    echo '</tr>';
    
    // Data siswa
    $no = 1;
    while ($siswa = mysqli_fetch_assoc($result_siswa)) {
        // Ambil nilai siswa
        $query_nilai = "SELECT * FROM nilai 
                        WHERE id_siswa = {$siswa['id_siswa']} 
                        AND id_mapel = '$filter_mapel' 
                        AND id_semester = {$semester_aktif['id_semester']}
                        AND id_guru = $id_user";
        $nilai = mysqli_fetch_assoc(mysqli_query($conn, $query_nilai));
        
        echo '<tr>';
        echo '<td style="text-align:center;">' . $no++ . '</td>';
        echo '<td>' . $siswa['nis'] . '</td>';
        echo '<td>' . $siswa['nama_lengkap'] . '</td>';
        
        // Formatif 1-4
        echo '<td style="text-align:center;">' . ($nilai && $nilai['nilai_formatif_1'] ? number_format($nilai['nilai_formatif_1'], 2) : '-') . '</td>';
        echo '<td style="text-align:center;">' . ($nilai && $nilai['nilai_formatif_2'] ? number_format($nilai['nilai_formatif_2'], 2) : '-') . '</td>';
        echo '<td style="text-align:center;">' . ($nilai && $nilai['nilai_formatif_3'] ? number_format($nilai['nilai_formatif_3'], 2) : '-') . '</td>';
        echo '<td style="text-align:center;">' . ($nilai && $nilai['nilai_formatif_4'] ? number_format($nilai['nilai_formatif_4'], 2) : '-') . '</td>';
        
        // STS & SAS
        echo '<td style="text-align:center;">' . ($nilai && $nilai['nilai_sts'] ? number_format($nilai['nilai_sts'], 2) : '-') . '</td>';
        echo '<td style="text-align:center;">' . ($nilai && $nilai['nilai_sas'] ? number_format($nilai['nilai_sas'], 2) : '-') . '</td>';
        
        // Rata-rata
        if ($nilai && $nilai['nilai_akhir']) {
            $rata_rata = number_format($nilai['nilai_akhir'], 2);
            $status = $nilai['nilai_akhir'] >= $mapel['kkm'] ? 'Tuntas' : 'Belum Tuntas';
            $color = $nilai['nilai_akhir'] >= $mapel['kkm'] ? 'green' : 'red';
            echo '<td style="text-align:center;font-weight:bold;color:' . $color . ';">' . $rata_rata . ' (' . $status . ')</td>';
        } else {
            echo '<td style="text-align:center;">-</td>';
        }
        
        // Deskripsi
        echo '<td>' . ($nilai && $nilai['deskripsi'] ? $nilai['deskripsi'] : '-') . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
    // Footer
    echo '<br><br>';
    echo '<table border="0">';
    echo '<tr><td colspan="11"></td></tr>';
    echo '<tr><td colspan="11"><strong>Keterangan:</strong></td></tr>';
    echo '<tr><td>F1-F4</td><td>: Nilai Formatif 1 sampai 4</td></tr>';
    echo '<tr><td>STS</td><td>: Sumatif Tengah Semester</td></tr>';
    echo '<tr><td>SAS</td><td>: Sumatif Akhir Semester</td></tr>';
    echo '<tr><td>Rata-rata</td><td>: Nilai Akhir (Rata-rata dari 6 komponen)</td></tr>';
    echo '<tr><td>KKM</td><td>: ' . $mapel['kkm'] . '</td></tr>';
    echo '</table>';
    
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Nilai Excel - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Download Nilai (Excel)</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Pilih Kelas dan Mata Pelajaran</h3>
                </div>
                <div class="card-body">
                    <?php if ($semester_aktif): ?>
                        <p><strong>Semester Aktif:</strong> <?php echo $semester_aktif['nama_semester']; ?></p>
                        <br>
                        <form method="GET" action="">
                            <div class="form-group">
                                <label for="rombel">Rombel *</label>
                                <select id="rombel" name="rombel" class="form-control" required>
                                    <option value="">-- Pilih Rombel --</option>
                                    <?php 
                                    mysqli_data_seek($result_mapel_guru, 0);
                                    $rombel_added = [];
                                    while ($mg = mysqli_fetch_assoc($result_mapel_guru)): 
                                        if (!in_array($mg['id_rombel'], $rombel_added)):
                                            $rombel_added[] = $mg['id_rombel'];
                                    ?>
                                        <option value="<?php echo $mg['id_rombel']; ?>" 
                                            <?php echo $filter_rombel == $mg['id_rombel'] ? 'selected' : ''; ?>>
                                            <?php echo $mg['nama_rombel']; ?>
                                        </option>
                                    <?php 
                                        endif;
                                    endwhile; 
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="mapel">Mata Pelajaran *</label>
                                <select id="mapel" name="mapel" class="form-control" required>
                                    <option value="">-- Pilih Mapel --</option>
                                    <?php 
                                    mysqli_data_seek($result_mapel_guru, 0);
                                    $mapel_added = [];
                                    while ($mg = mysqli_fetch_assoc($result_mapel_guru)): 
                                        if (!in_array($mg['id_mapel'], $mapel_added)):
                                            $mapel_added[] = $mg['id_mapel'];
                                    ?>
                                        <option value="<?php echo $mg['id_mapel']; ?>" 
                                            <?php echo $filter_mapel == $mg['id_mapel'] ? 'selected' : ''; ?>>
                                            <?php echo $mg['nama_mapel']; ?>
                                        </option>
                                    <?php 
                                        endif;
                                    endwhile; 
                                    ?>
                                </select>
                            </div>
                            
                            <button type="submit" name="download" value="1" class="btn btn-success">
                                💾 Download Excel
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger">Tidak ada semester aktif. Hubungi administrator!</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Informasi</h3>
                </div>
                <div class="card-body">
                    <p>Fitur ini memungkinkan Anda untuk mengunduh nilai yang sudah Anda input dalam format Excel (.xls).</p>
                    <p>File Excel akan berisi:</p>
                    <ul>
                        <li>Daftar semua siswa dalam rombel yang dipilih</li>
                        <li>Nilai Formatif 1 sampai 4</li>
                        <li>Nilai STS (Sumatif Tengah Semester)</li>
                        <li>Nilai SAS (Sumatif Akhir Semester)</li>
                        <li>Rata-rata nilai (Nilai Akhir)</li>
                        <li>Deskripsi nilai untuk setiap siswa</li>
                        <li>Status ketuntasan (Tuntas/Belum Tuntas berdasarkan KKM)</li>
                    </ul>
                    <p><strong>Catatan:</strong> Hanya nilai yang Anda input sendiri yang akan ditampilkan.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
