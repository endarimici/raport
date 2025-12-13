<?php
require_once '../../config.php';
requireRole('administrator');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_jurusan = cleanInput($_POST['kode_jurusan']);
    $nama_jurusan = cleanInput($_POST['nama_jurusan']);
    $keterangan = cleanInput($_POST['keterangan']);
    
    if (empty($kode_jurusan) || empty($nama_jurusan)) {
        $error = 'Kode dan nama jurusan harus diisi!';
    } else {
        $check = "SELECT * FROM jurusan WHERE kode_jurusan = '$kode_jurusan'";
        $result = mysqli_query($conn, $check);
        
        if (mysqli_num_rows($result) > 0) {
            $error = 'Kode jurusan sudah digunakan!';
        } else {
            $query = "INSERT INTO jurusan (kode_jurusan, nama_jurusan, keterangan) 
                      VALUES ('$kode_jurusan', '$nama_jurusan', '$keterangan')";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Jurusan berhasil ditambahkan!';
            } else {
                $error = 'Gagal menambahkan jurusan!';
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
    <title>Tambah Jurusan - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Tambah Jurusan</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Form Tambah Jurusan</h3>
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
                            <label for="kode_jurusan">Kode Jurusan *</label>
                            <input type="text" id="kode_jurusan" name="kode_jurusan" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nama_jurusan">Nama Jurusan *</label>
                            <input type="text" id="nama_jurusan" name="nama_jurusan" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea id="keterangan" name="keterangan" class="form-control"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
