<?php
require_once '../config.php';
requireRole('wali_kelas');

// Ambil data wali kelas yang login
$id_user = $_SESSION['user_id'];
$nama_wali = $_SESSION['nama_lengkap'];

// Hitung berapa banyak rombel yang diasuh (berdasarkan nama wali kelas di tabel rombel)
$query_rombel = "SELECT COUNT(*) as total FROM rombel WHERE wali_kelas LIKE '%$nama_wali%'";
$result_rombel = mysqli_query($conn, $query_rombel);
$total_rombel = mysqli_fetch_assoc($result_rombel)['total'];

// Hitung total siswa dari rombel yang diasuh
$query_siswa = "SELECT COUNT(s.id_siswa) as total 
                FROM siswa s 
                INNER JOIN rombel r ON s.id_rombel = r.id_rombel 
                WHERE r.wali_kelas LIKE '%$nama_wali%' AND s.status = 'aktif'";
$result_siswa = mysqli_query($conn, $query_siswa);
$total_siswa = mysqli_fetch_assoc($result_siswa)['total'];

// Ambil daftar rombel yang diasuh
$query_list_rombel = "SELECT r.*, j.nama_jurusan 
                      FROM rombel r 
                      LEFT JOIN jurusan j ON r.id_jurusan = j.id_jurusan 
                      WHERE r.wali_kelas LIKE '%$nama_wali%' 
                      ORDER BY r.tingkat, r.nama_rombel";
$result_list_rombel = mysqli_query($conn, $query_list_rombel);

// Ambil semester aktif
$query_semester = "SELECT * FROM semester WHERE status = 'aktif' LIMIT 1";
$result_semester = mysqli_query($conn, $query_semester);
$semester_aktif = mysqli_fetch_assoc($result_semester);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Wali Kelas - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <div>
                    <h2>Dashboard Wali Kelas</h2>
                    <p style="margin:5px 0;color:#666;font-size:14px;">SMK MUHAMMADIYAH 8 PAKIS</p>
                </div>
                <div class="user-info">
                    <span>Selamat datang, <strong><?php echo $_SESSION['nama_lengkap']; ?></strong></span>
                    <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🏫</div>
                    <div class="stat-info">
                        <h3><?php echo $total_rombel; ?></h3>
                        <p>Rombel Diasuh</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-info">
                        <h3><?php echo $total_siswa; ?></h3>
                        <p>Total Siswa</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-info">
                        <h3><?php echo $semester_aktif ? $semester_aktif['nama_semester'] : '-'; ?></h3>
                        <p>Semester Aktif</p>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Rombel yang Diasuh</h3>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result_list_rombel) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Rombel</th>
                                        <th>Jurusan</th>
                                        <th>Tingkat</th>
                                        <th>Tahun Ajaran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    while ($rombel = mysqli_fetch_assoc($result_list_rombel)): 
                                    ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $rombel['nama_rombel']; ?></td>
                                            <td><?php echo $rombel['nama_jurusan']; ?></td>
                                            <td><?php echo $rombel['tingkat']; ?></td>
                                            <td><?php echo $rombel['tahun_ajaran']; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">Belum ada rombel yang diasuh.</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Informasi</h3>
                </div>
                <div class="card-body">
                    <p>Selamat datang di Dashboard Wali Kelas. Sebagai wali kelas, Anda dapat:</p>
                    <ul>
                        <li>Melihat informasi rombel yang Anda asuh</li>
                        <li>Mengunduh raport siswa dalam format Excel</li>
                    </ul>
                    <p><strong>Catatan:</strong> Untuk mengunduh raport, gunakan menu "Download Raport" di sidebar.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
