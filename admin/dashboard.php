<?php
require_once '../config.php';
requireRole('administrator');

// Statistik
$query_users = "SELECT COUNT(*) as total FROM users WHERE status = 'aktif'";
$result_users = mysqli_query($conn, $query_users);
$total_users = mysqli_fetch_assoc($result_users)['total'];

$query_siswa = "SELECT COUNT(*) as total FROM siswa WHERE status = 'aktif'";
$result_siswa = mysqli_query($conn, $query_siswa);
$total_siswa = mysqli_fetch_assoc($result_siswa)['total'];

$query_rombel = "SELECT COUNT(*) as total FROM rombel";
$result_rombel = mysqli_query($conn, $query_rombel);
$total_rombel = mysqli_fetch_assoc($result_rombel)['total'];

$query_mapel = "SELECT COUNT(*) as total FROM mata_pelajaran";
$result_mapel = mysqli_query($conn, $query_mapel);
$total_mapel = mysqli_fetch_assoc($result_mapel)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrator - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <div>
                    <h2>Dashboard Administrator</h2>
                    <p style="margin:5px 0;color:#666;font-size:14px;">SMK MUHAMMADIYAH 8 PAKIS</p>
                </div>
                <div class="user-info">
                    <span>Selamat datang, <strong><?php echo $_SESSION['nama_lengkap']; ?></strong></span>
                    <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="stats-container">
                <div class="stat-card primary">
                    <div class="stat-card-content">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Pengguna</p>
                    </div>
                    <div class="stat-card-icon">👥</div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-card-content">
                        <h3><?php echo $total_siswa; ?></h3>
                        <p>Total Siswa</p>
                    </div>
                    <div class="stat-card-icon">👨‍🎓</div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-card-content">
                        <h3><?php echo $total_rombel; ?></h3>
                        <p>Total Rombel</p>
                    </div>
                    <div class="stat-card-icon">🏫</div>
                </div>
                
                <div class="stat-card danger">
                    <div class="stat-card-content">
                        <h3><?php echo $total_mapel; ?></h3>
                        <p>Total Mata Pelajaran</p>
                    </div>
                    <div class="stat-card-icon">📚</div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Informasi Sistem</h3>
                </div>
                <div class="card-body">
                    <p>Selamat datang di <strong>Aplikasi Raport SMK</strong></p>
                    <p>Sistem ini membantu Anda mengelola data raport siswa dengan mudah dan terstruktur.</p>
                    <br>
                    <h4>Fitur yang tersedia:</h4>
                    <ul>
                        <li>Manajemen Pengguna (Administrator & Guru)</li>
                        <li>Manajemen Jurusan</li>
                        <li>Manajemen Rombongan Belajar</li>
                        <li>Manajemen Mata Pelajaran</li>
                        <li>Manajemen Data Siswa</li>
                        <li>Manajemen Semester & Tahun Akademik</li>
                        <li>Input dan Pengelolaan Nilai Siswa</li>
                        <li>Download Raport Siswa (Format Excel)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
