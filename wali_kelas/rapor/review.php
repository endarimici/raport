<?php

require_once '../../config.php';

//requireRole(['administrator', 'wali_kelas']);

$id_siswa = isset($_GET['id']) ? cleanInput($_GET['id']) : '';
$id_semester = isset($_GET['semester']) ? cleanInput($_GET['semester']) : '';

if(!$id_siswa) {
    die("ID Siswa tidak valid");
}

if(!$id_semester) {
    die("Semester harus dipilih");
}

// Cek akses jika wali kelas
if($_SESSION['role'] == 'wali_kelas') {
    $id_wali_kelas = $_SESSION['user_id'];
    $query_check = "SELECT s.* FROM siswa s 
                    INNER JOIN rombel r ON s.id_rombel = r.id_rombel 
                    WHERE s.id_siswa = '$id_siswa' AND r.id_wali_kelas = '$id_wali_kelas'";
    $result_check = mysqli_query($conn, $query_check);
    if(mysqli_num_rows($result_check) == 0) {
        die("Anda tidak memiliki akses untuk me-review rapor siswa ini");
    }
}

// Ambil data siswa
$query_siswa = "SELECT s.*, r.nama_rombel, j.nama_jurusan
                FROM siswa s
                INNER JOIN rombel r ON s.id_rombel = r.id_rombel
                INNER JOIN jurusan j ON r.id_jurusan = j.id_jurusan
                WHERE s.id_siswa = '$id_siswa'";
$result_siswa = mysqli_query($conn, $query_siswa);
$siswa = mysqli_fetch_assoc($result_siswa);

if(!$siswa) {
    die("Data siswa tidak ditemukan");
}

// Ambil data semester
$query_semester = "SELECT * FROM semester WHERE id_semester = '$id_semester'";
$result_semester = mysqli_query($conn, $query_semester);
$semester = mysqli_fetch_assoc($result_semester);

// Ambil data rapor tambahan jika sudah ada
$query_rapor = "SELECT * FROM rapor_tambahan WHERE id_siswa = '$id_siswa' AND id_semester = '$id_semester'";
$result_rapor = mysqli_query($conn, $query_rapor);
$rapor_tambahan = mysqli_fetch_assoc($result_rapor);

// Ambil data ekstrakurikuler
$query_ekskul = "SELECT * FROM rapor_ekstrakurikuler WHERE id_siswa = '$id_siswa' AND id_semester = '$id_semester' ORDER BY id_ekstrakurikuler";
$result_ekskul = mysqli_query($conn, $query_ekskul);

// Handle form submit
$message = '';
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $deskripsi_kokurikuler = cleanInput($_POST['deskripsi_kokurikuler']);
    $sakit = (int)$_POST['sakit'];
    $izin = (int)$_POST['izin'];
    $tanpa_keterangan = (int)$_POST['tanpa_keterangan'];
    $catatan_wali_kelas = cleanInput($_POST['catatan_wali_kelas']);
    
    // Insert atau update rapor_tambahan
    if($rapor_tambahan) {
        $query_update = "UPDATE rapor_tambahan SET 
                        deskripsi_kokurikuler = '$deskripsi_kokurikuler',
                        sakit = $sakit,
                        izin = $izin,
                        tanpa_keterangan = $tanpa_keterangan,
                        catatan_wali_kelas = '$catatan_wali_kelas'
                        WHERE id_siswa = '$id_siswa' AND id_semester = '$id_semester'";
        mysqli_query($conn, $query_update);
    } else {
        $query_insert = "INSERT INTO rapor_tambahan (id_siswa, id_semester, deskripsi_kokurikuler, sakit, izin, tanpa_keterangan, catatan_wali_kelas) 
                        VALUES ('$id_siswa', '$id_semester', '$deskripsi_kokurikuler', $sakit, $izin, $tanpa_keterangan, '$catatan_wali_kelas')";
        mysqli_query($conn, $query_insert);
    }
    
    // Hapus ekstrakurikuler lama
    mysqli_query($conn, "DELETE FROM rapor_ekstrakurikuler WHERE id_siswa = '$id_siswa' AND id_semester = '$id_semester'");
    
    // Insert ekstrakurikuler baru
    if(isset($_POST['nama_ekstrakurikuler'])) {
        foreach($_POST['nama_ekstrakurikuler'] as $index => $nama_ekskul) {
            $nama_ekskul = cleanInput($nama_ekskul);
            $keterangan_ekskul = cleanInput($_POST['keterangan_ekstrakurikuler'][$index]);
            
            if(!empty($nama_ekskul)) {
                $query_ekskul_insert = "INSERT INTO rapor_ekstrakurikuler (id_siswa, id_semester, nama_ekstrakurikuler, keterangan) 
                                       VALUES ('$id_siswa', '$id_semester', '$nama_ekskul', '$keterangan_ekskul')";
                mysqli_query($conn, $query_ekskul_insert);
            }
        }
    }
    
    $message = '<div class="alert alert-success">Data rapor berhasil disimpan!</div>';
    
    // Refresh data
    $result_rapor = mysqli_query($conn, $query_rapor);
    $rapor_tambahan = mysqli_fetch_assoc($result_rapor);
    $result_ekskul = mysqli_query($conn, $query_ekskul);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Rapor - <?php echo htmlspecialchars($siswa['nama_lengkap']); ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .form-section h4 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .ekskul-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: flex-start;
        }
        .ekskul-row input[type="text"] {
            flex: 1;
        }
        .ekskul-row textarea {
            flex: 2;
        }
        .btn-remove {
            background-color: #f44336;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn-add {
            background-color: #4CAF50;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-top: 10px;
        }
        .kehadiran-group {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Review Rapor</h2>
                <div class="user-info">
                    <span>👤 <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?> (<?php echo ucfirst($_SESSION['role']); ?>)</span>
                    <a href="<?php echo BASE_URL; ?>logout.php" class="btn-logout">Logout</a>
                </div>
            </div>
            
            <div class="content">
                <?php echo $message; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Review Rapor - <?php echo htmlspecialchars($siswa['nama_lengkap']); ?></h3>
                    </div>
                    <div class="card-body">
                        <div style="margin-bottom: 20px;">
                            <table style="width: auto;">
                                <tr>
                                    <td><strong>NIS</strong></td>
                                    <td style="padding: 0 10px;">:</td>
                                    <td><?php echo htmlspecialchars($siswa['nis']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nama</strong></td>
                                    <td style="padding: 0 10px;">:</td>
                                    <td><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Rombel</strong></td>
                                    <td style="padding: 0 10px;">:</td>
                                    <td><?php echo htmlspecialchars($siswa['nama_rombel']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Semester</strong></td>
                                    <td style="padding: 0 10px;">:</td>
                                    <td><?php echo htmlspecialchars($semester['nama_semester']); ?></td>
                                </tr>
                            </table>
                        </div>
                        
                        <form method="POST" action="">
                            <!-- Kokurikuler -->
                            <div class="form-section">
                                <h4>📚 Deskripsi Kokurikuler</h4>
                                <div class="form-group">
                                    <label>Deskripsi:</label>
                                    <textarea name="deskripsi_kokurikuler" class="form-control" rows="4" placeholder="Contoh: Ananda sudah baik dalam kreativitas yang terlihat dari kemampuan menemukan dan mengembangkan alternatif solusi yang efektif..."><?php echo htmlspecialchars($rapor_tambahan['deskripsi_kokurikuler'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Ekstrakurikuler -->
                            <div class="form-section">
                                <h4>🏆 Ekstrakurikuler</h4>
                                <div id="ekskul-container">
                                    <?php 
                                    $ekskul_count = 0;
                                    if(mysqli_num_rows($result_ekskul) > 0) {
                                        while($ekskul = mysqli_fetch_assoc($result_ekskul)):
                                            $ekskul_count++;
                                    ?>
                                    <div class="ekskul-row">
                                        <input type="text" name="nama_ekstrakurikuler[]" class="form-control" placeholder="Nama Ekstrakurikuler" value="<?php echo htmlspecialchars($ekskul['nama_ekstrakurikuler']); ?>">
                                        <textarea name="keterangan_ekstrakurikuler[]" class="form-control" rows="2" placeholder="Keterangan"><?php echo htmlspecialchars($ekskul['keterangan']); ?></textarea>
                                        <button type="button" class="btn-remove" onclick="removeEkskul(this)">✖</button>
                                    </div>
                                    <?php 
                                        endwhile;
                                    }
                                    
                                    // Jika belum ada data, tampilkan 2 baris kosong
                                    if($ekskul_count == 0) {
                                        for($i = 0; $i < 2; $i++):
                                    ?>
                                    <div class="ekskul-row">
                                        <input type="text" name="nama_ekstrakurikuler[]" class="form-control" placeholder="Nama Ekstrakurikuler">
                                        <textarea name="keterangan_ekstrakurikuler[]" class="form-control" rows="2" placeholder="Keterangan"></textarea>
                                        <button type="button" class="btn-remove" onclick="removeEkskul(this)">✖</button>
                                    </div>
                                    <?php 
                                        endfor;
                                    }
                                    ?>
                                </div>
                                <button type="button" class="btn-add" onclick="addEkskul()">+ Tambah Ekstrakurikuler</button>
                            </div>
                            
                            <!-- Ketidakhadiran -->
                            <div class="form-section">
                                <h4>📅 Ketidakhadiran</h4>
                                <div class="kehadiran-group">
                                    <div class="form-group">
                                        <label>Sakit (hari):</label>
                                        <input type="number" name="sakit" class="form-control" min="0" value="<?php echo htmlspecialchars($rapor_tambahan['sakit'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Izin (hari):</label>
                                        <input type="number" name="izin" class="form-control" min="0" value="<?php echo htmlspecialchars($rapor_tambahan['izin'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Tanpa Keterangan (hari):</label>
                                        <input type="number" name="tanpa_keterangan" class="form-control" min="0" value="<?php echo htmlspecialchars($rapor_tambahan['tanpa_keterangan'] ?? 0); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Catatan Wali Kelas -->
                            <div class="form-section">
                                <h4>📝 Catatan Wali Kelas</h4>
                                <div class="form-group">
                                    <label>Catatan:</label>
                                    <textarea name="catatan_wali_kelas" class="form-control" rows="4" placeholder="Masukkan catatan untuk siswa..."><?php echo htmlspecialchars($rapor_tambahan['catatan_wali_kelas'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div style="margin-top: 20px;">
                                <button type="submit" name="submit" class="btn btn-primary">💾 Simpan Data Rapor</button>
                                <a href="index.php?semester=<?php echo $id_semester; ?>" class="btn btn-secondary">← Kembali</a>
                                <a href="preview.php?id=<?php echo $id_siswa; ?>&semester=<?php echo $id_semester; ?>" class="btn btn-success" target="_blank">👁️ Preview Rapor</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function addEkskul() {
            const container = document.getElementById('ekskul-container');
            const newRow = document.createElement('div');
            newRow.className = 'ekskul-row';
            newRow.innerHTML = `
                <input type="text" name="nama_ekstrakurikuler[]" class="form-control" placeholder="Nama Ekstrakurikuler">
                <textarea name="keterangan_ekstrakurikuler[]" class="form-control" rows="2" placeholder="Keterangan"></textarea>
                <button type="button" class="btn-remove" onclick="removeEkskul(this)">✖</button>
            `;
            container.appendChild(newRow);
        }
        
        function removeEkskul(btn) {
            btn.parentElement.remove();
        }
    </script>
</body>
</html>
