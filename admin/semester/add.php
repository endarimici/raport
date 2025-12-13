<?php
require_once '../../config.php';
requireRole('administrator');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_semester = cleanInput($_POST['nama_semester']);
    $semester = cleanInput($_POST['semester']);
    $tahun_ajaran = cleanInput($_POST['tahun_ajaran']);
    $tanggal_mulai = cleanInput($_POST['tanggal_mulai']);
    $tanggal_selesai = cleanInput($_POST['tanggal_selesai']);
    $status = cleanInput($_POST['status']);
    
    if (empty($nama_semester) || empty($semester) || empty($tahun_ajaran) || empty($tanggal_mulai) || empty($tanggal_selesai)) {
        $error = 'Semua field harus diisi!';
    } else {
        // Jika status aktif, set semua semester lain jadi nonaktif
        if ($status == 'aktif') {
            mysqli_query($conn, "UPDATE semester SET status = 'nonaktif'");
        }
        
        $query = "INSERT INTO semester (nama_semester, semester, tahun_ajaran, tanggal_mulai, tanggal_selesai, status) 
                  VALUES ('$nama_semester', '$semester', '$tahun_ajaran', '$tanggal_mulai', '$tanggal_selesai', '$status')";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Semester berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan semester!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Semester - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Tambah Semester</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Form Tambah Semester</h3>
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
                            <label for="nama_semester">Nama Semester *</label>
                            <input type="text" id="nama_semester" name="nama_semester" class="form-control" 
                                   placeholder="Contoh: Semester Ganjil 2024/2025" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="semester">Semester *</label>
                            <select id="semester" name="semester" class="form-control" required>
                                <option value="">-- Pilih Semester --</option>
                                <option value="Ganjil">Ganjil</option>
                                <option value="Genap">Genap</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="tahun_ajaran">Tahun Ajaran *</label>
                            <input type="text" id="tahun_ajaran" name="tahun_ajaran" class="form-control" 
                                   placeholder="Contoh: 2024/2025" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal_mulai">Tanggal Mulai *</label>
                            <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal_selesai">Tanggal Selesai *</label>
                            <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="nonaktif">Non Aktif</option>
                                <option value="aktif">Aktif</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
