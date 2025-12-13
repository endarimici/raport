<?php
require_once '../config.php';
requireRole('guru');

// Ambil data guru yang login
$id_user = $_SESSION['user_id'];

// Hitung berapa banyak kelas yang diajar
$query_kelas = "SELECT COUNT(DISTINCT id_rombel) as total FROM mapel_guru WHERE id_user = $id_user";
$result_kelas = mysqli_query($conn, $query_kelas);
$total_kelas = mysqli_fetch_assoc($result_kelas)['total'];

// Hitung berapa banyak mapel yang diajar
$query_mapel = "SELECT COUNT(DISTINCT id_mapel) as total FROM mapel_guru WHERE id_user = $id_user";
$result_mapel = mysqli_query($conn, $query_mapel);
$total_mapel = mysqli_fetch_assoc($result_mapel)['total'];

// Hitung total siswa dari semua kelas yang diajar
$query_siswa = "SELECT COUNT(DISTINCT s.id_siswa) as total 
                FROM siswa s 
                INNER JOIN mapel_guru mg ON s.id_rombel = mg.id_rombel 
                WHERE mg.id_user = $id_user AND s.status = 'aktif'";
$result_siswa = mysqli_query($conn, $query_siswa);
$total_siswa = mysqli_fetch_assoc($result_siswa)['total'];

// Hitung nilai yang sudah diinput
$query_nilai = "SELECT COUNT(*) as total FROM nilai WHERE id_guru = $id_user";
$result_nilai = mysqli_query($conn, $query_nilai);
$total_nilai = mysqli_fetch_assoc($result_nilai)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <div>
                    <h2>Dashboard Guru</h2>
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
                        <h3><?php echo $total_kelas; ?></h3>
                        <p>Kelas yang Diajar</p>
                    </div>
                    <div class="stat-card-icon">🏫</div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-card-content">
                        <h3><?php echo $total_mapel; ?></h3>
                        <p>Mata Pelajaran</p>
                    </div>
                    <div class="stat-card-icon">📚</div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-card-content">
                        <h3><?php echo $total_siswa; ?></h3>
                        <p>Total Siswa</p>
                    </div>
                    <div class="stat-card-icon">👨‍🎓</div>
                </div>
                
                <div class="stat-card danger">
                    <div class="stat-card-content">
                        <h3><?php echo $total_nilai; ?></h3>
                        <p>Nilai Terinput</p>
                    </div>
                    <div class="stat-card-icon">📝</div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Informasi</h3>
                </div>
                <div class="card-body">
                    <p>Selamat datang di <strong>Aplikasi Raport SMK</strong></p>
                    <p>Sebagai guru, Anda dapat mengelola nilai siswa untuk mata pelajaran yang Anda ampu.</p>
                    <br>
                    <h4>Fitur yang tersedia:</h4>
                    <ul>
                        <li>Input Nilai Siswa</li>
                        <li>Edit Nilai Siswa</li>
                        <li>Download Raport Siswa (Format Excel)</li>
                        <li>Lihat Data Siswa</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
