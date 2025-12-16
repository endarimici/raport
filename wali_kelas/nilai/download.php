<?php
require_once '../../config.php';
requireRole('wali_kelas');

// Ambil data semester
$query_semester = "SELECT * FROM semester ORDER BY id_semester DESC";
$result_semester = mysqli_query($conn, $query_semester);

// Ambil data rombel
$query_rombel = "SELECT * FROM rombel ORDER BY nama_rombel";
$result_rombel = mysqli_query($conn, $query_rombel);

// Filter
$filter_semester = isset($_GET['semester']) ? cleanInput($_GET['semester']) : '';
$filter_rombel = isset($_GET['rombel']) ? cleanInput($_GET['rombel']) : '';

// Proses download
if (isset($_GET['download']) && $filter_semester && $filter_rombel) {
    // Ambil data semester dan rombel
    $query_sem = "SELECT * FROM semester WHERE id_semester = $filter_semester";
    $semester = mysqli_fetch_assoc(mysqli_query($conn, $query_sem));
    
    $query_rmb = "SELECT r.*, j.nama_jurusan FROM rombel r 
                  LEFT JOIN jurusan j ON r.id_jurusan = j.id_jurusan 
                  WHERE r.id_rombel = $filter_rombel";
    $rombel = mysqli_fetch_assoc(mysqli_query($conn, $query_rmb));
    
    // Ambil data siswa
    $query_siswa = "SELECT * FROM siswa WHERE id_rombel = $filter_rombel AND status = 'aktif' ORDER BY nama_lengkap";
    $result_siswa = mysqli_query($conn, $query_siswa);
    
    // Ambil data mata pelajaran
    $query_mapel = "SELECT * FROM mata_pelajaran ORDER BY kelompok, nama_mapel";
    $result_mapel = mysqli_query($conn, $query_mapel);
    $mapel_list = [];
    while ($m = mysqli_fetch_assoc($result_mapel)) {
        $mapel_list[] = $m;
    }
    
    // Set header untuk download Excel
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Raport_{$rombel['nama_rombel']}_{$semester['nama_semester']}.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    echo '<table border="1">';
    echo '<tr><td colspan="' . (count($mapel_list) + 6) . '" style="text-align:center;font-weight:bold;font-size:18px;">SMK MUHAMMADIYAH 8 PAKIS</td></tr>';
    echo '<tr><td colspan="' . (count($mapel_list) + 6) . '" style="text-align:center;font-weight:bold;font-size:16px;">LAPORAN HASIL BELAJAR SISWA (RAPORT)</td></tr>';
    echo '<tr><td colspan="' . (count($mapel_list) + 6) . '"></td></tr>';
    echo '<tr><td>Kelas/Rombel</td><td colspan="2">: ' . $rombel['nama_rombel'] . '</td><td>Semester</td><td colspan="2">: ' . $semester['nama_semester'] . '</td></tr>';
    echo '<tr><td>Jurusan</td><td colspan="2">: ' . $rombel['nama_jurusan'] . '</td><td>Tahun Ajaran</td><td colspan="2">: ' . $semester['tahun_ajaran'] . '</td></tr>';
    echo '<tr><td colspan="' . (count($mapel_list) + 6) . '"></td></tr>';
    
    // Header tabel
    echo '<tr style="background-color: #4472C4; color: white; font-weight: bold;">';
    echo '<td rowspan="2" style="text-align:center;">No</td>';
    echo '<td rowspan="2" style="text-align:center;">NIS</td>';
    echo '<td rowspan="2" style="text-align:center;">Nama Siswa</td>';
    
    foreach ($mapel_list as $mapel) {
        echo '<td colspan="2" style="text-align:center;">' . $mapel['nama_mapel'] . ' (KKM: ' . $mapel['kkm'] . ')</td>';
    }
    
    echo '<td rowspan="2" style="text-align:center;">Rata-rata</td>';
    echo '</tr>';
    
    echo '<tr style="background-color: #4472C4; color: white; font-weight: bold;">';
    foreach ($mapel_list as $mapel) {
        echo '<td style="text-align:center;">Nilai</td>';
        echo '<td style="text-align:center;">Keterangan</td>';
    }
    echo '</tr>';
    
    // Data siswa
    $no = 1;
    while ($siswa = mysqli_fetch_assoc($result_siswa)) {
        echo '<tr>';
        echo '<td style="text-align:center;">' . $no++ . '</td>';
        echo '<td>' . $siswa['nis'] . '</td>';
        echo '<td>' . $siswa['nama_lengkap'] . '</td>';
        
        $total_nilai = 0;
        $jumlah_mapel = 0;
        
        foreach ($mapel_list as $mapel) {
            // Ambil nilai siswa untuk mapel ini
            $query_nilai = "SELECT * FROM nilai 
                            WHERE id_siswa = {$siswa['id_siswa']} 
                            AND id_mapel = {$mapel['id_mapel']} 
                            AND id_semester = $filter_semester";
            $nilai = mysqli_fetch_assoc(mysqli_query($conn, $query_nilai));
            
            if ($nilai) {
                echo '<td style="text-align:center;font-weight:bold;">' . number_format($nilai['nilai_akhir'], 2) . '</td>';
                
                // Status KKM
                $status = $nilai['nilai_akhir'] >= $mapel['kkm'] ? 'Tuntas' : 'Belum Tuntas';
                $color = $nilai['nilai_akhir'] >= $mapel['kkm'] ? 'green' : 'red';
                echo '<td style="text-align:center;color:' . $color . ';font-weight:bold;">' . $status . '</td>';
                
                $total_nilai += $nilai['nilai_akhir'];
                $jumlah_mapel++;
            } else {
                echo '<td style="text-align:center;">-</td>';
                echo '<td style="text-align:center;">-</td>';
            }
        }
        
        $rata_rata = $jumlah_mapel > 0 ? $total_nilai / $jumlah_mapel : 0;
        echo '<td style="text-align:center; font-weight:bold;">' . number_format($rata_rata, 2) . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Raport - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Download Raport</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Pilih Semester dan Rombel</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="form-group">
                            <label for="semester">Semester *</label>
                            <select id="semester" name="semester" class="form-control" required>
                                <option value="">-- Pilih Semester --</option>
                                <?php while ($sem = mysqli_fetch_assoc($result_semester)): ?>
                                    <option value="<?php echo $sem['id_semester']; ?>" 
                                        <?php echo $filter_semester == $sem['id_semester'] ? 'selected' : ''; ?>>
                                        <?php echo $sem['nama_semester']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="rombel">Rombel *</label>
                            <select id="rombel" name="rombel" class="form-control" required>
                                <option value="">-- Pilih Rombel --</option>
                                <?php while ($rmb = mysqli_fetch_assoc($result_rombel)): ?>
                                    <option value="<?php echo $rmb['id_rombel']; ?>" 
                                        <?php echo $filter_rombel == $rmb['id_rombel'] ? 'selected' : ''; ?>>
                                        <?php echo $rmb['nama_rombel']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <button type="submit" name="download" value="1" class="btn btn-success">
                            💾 Download Raport (Excel)
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Informasi</h3>
                </div>
                <div class="card-body">
                    <p>Fitur ini memungkinkan Anda untuk mengunduh raport siswa dalam format Excel (.xls).</p>
                    <p>Raport akan berisi:</p>
                    <ul>
                        <li>Daftar semua siswa dalam rombel yang dipilih</li>
                        <li>Nilai untuk setiap mata pelajaran</li>
                        <li>Status ketuntasan untuk setiap mata pelajaran (Tuntas/Belum Tuntas)</li>
                        <li>Rata-rata nilai keseluruhan</li>
                    </ul>
                    <p><strong>Catatan:</strong> Pastikan semester dan rombel sudah memiliki data nilai sebelum download.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
