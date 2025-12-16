<?php
require_once '../../config.php';
requireRole('administrator');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_mapel = cleanInput($_POST['kode_mapel']);
    $nama_mapel = cleanInput($_POST['nama_mapel']);
    $kelompok = cleanInput($_POST['kelompok']);
    $kkm = cleanInput($_POST['kkm']);
    $deskripsi_a = cleanInput($_POST['deskripsi_a']);
    $deskripsi_b = cleanInput($_POST['deskripsi_b']);
    $deskripsi_c = cleanInput($_POST['deskripsi_c']);
    $deskripsi_d = cleanInput($_POST['deskripsi_d']);
    
    if (empty($kode_mapel) || empty($nama_mapel) || empty($kelompok)) {
        $error = 'Kode, nama, dan kelompok mata pelajaran harus diisi!';
    } else {
        $check = "SELECT * FROM mata_pelajaran WHERE kode_mapel = '$kode_mapel'";
        $result = mysqli_query($conn, $check);
        
        if (mysqli_num_rows($result) > 0) {
            $error = 'Kode mata pelajaran sudah digunakan!';
        } else {
            $query = "INSERT INTO mata_pelajaran (kode_mapel, nama_mapel, kelompok, kkm, deskripsi_a, deskripsi_b, deskripsi_c, deskripsi_d) 
                      VALUES ('$kode_mapel', '$nama_mapel', '$kelompok', '$kkm', '$deskripsi_a', '$deskripsi_b', '$deskripsi_c', '$deskripsi_d')";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Mata pelajaran berhasil ditambahkan!';
            } else {
                $error = 'Gagal menambahkan mata pelajaran!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mata Pelajaran - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Tambah Mata Pelajaran</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Form Tambah Mata Pelajaran</h3>
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="kode_mapel">Kode Mata Pelajaran *</label>
                            <input type="text" id="kode_mapel" name="kode_mapel" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nama_mapel">Nama Mata Pelajaran *</label>
                            <input type="text" id="nama_mapel" name="nama_mapel" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="kelompok">Kelompok *</label>
                            <select id="kelompok" name="kelompok" class="form-control" required>
                                <option value="">-- Pilih Kelompok --</option>
                                <option value="A">A - Umum</option>
                                <option value="B">B - Kejuruan</option>
                                <option value="C">C - Muatan Lokal</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="kkm">KKM *</label>
                            <input type="number" id="kkm" name="kkm" class="form-control" 
                                   value="75" step="0.01" min="0" max="100" required>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Deskripsi Predikat Nilai:</strong><br>
                            Isi deskripsi untuk setiap predikat yang akan muncul di rapor siswa.
                        </div>
                        
                        <div class="form-group">
                            <label for="deskripsi_a">Deskripsi Predikat A (90-100)</label>
                            <textarea id="deskripsi_a" name="deskripsi_a" class="form-control" rows="3" 
                                      placeholder="Contoh: Siswa menunjukkan pemahaman yang sangat baik dan mampu menerapkan konsep dengan sangat efektif."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="deskripsi_b">Deskripsi Predikat B (80-89)</label>
                            <textarea id="deskripsi_b" name="deskripsi_b" class="form-control" rows="3"
                                      placeholder="Contoh: Siswa menunjukkan pemahaman yang baik dan mampu menerapkan konsep dengan cukup efektif."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="deskripsi_c">Deskripsi Predikat C (70-79)</label>
                            <textarea id="deskripsi_c" name="deskripsi_c" class="form-control" rows="3"
                                      placeholder="Contoh: Siswa menunjukkan pemahaman yang cukup dan perlu lebih banyak latihan dalam menerapkan konsep."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="deskripsi_d">Deskripsi Predikat D (< 70)</label>
                            <textarea id="deskripsi_d" name="deskripsi_d" class="form-control" rows="3"
                                      placeholder="Contoh: Siswa perlu bimbingan lebih lanjut untuk memahami dan menerapkan konsep dasar."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
