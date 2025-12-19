<?php
require_once '../../config.php';
requireRole('administrator');

if (isset($_GET['delete'])) {
    $id = cleanInput($_GET['delete']);
    $query = "DELETE FROM mata_pelajaran WHERE id_mapel = $id";
    if (mysqli_query($conn, $query)) {
        $success = "Mata pelajaran berhasil dihapus!";
    } else {
        $error = "Gagal menghapus mata pelajaran!";
    }
}

$query = "SELECT * FROM mata_pelajaran ORDER BY urutan ASC, id_mapel DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mata Pelajaran - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Data Mata Pelajaran</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Mata Pelajaran</h3>
                    <a href="add.php" class="btn btn-primary">Tambah Mata Pelajaran</a>
                </div>
                <div class="card-body">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Urutan</th>
                                    <th>Kode Mapel</th>
                                    <th>Nama Mata Pelajaran</th>
                                    <th>Kelompok</th>
                                    <th>KKM</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)): 
                                    $kelompok_nama = ['A' => 'Umum', 'B' => 'Kejuruan', 'C' => 'Muatan Lokal'];
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo $row['urutan'] ? $row['urutan'] : '-'; ?></td>
                                    <td><?php echo $row['kode_mapel']; ?></td>
                                    <td><?php echo $row['nama_mapel']; ?></td>
                                    <td><?php echo $kelompok_nama[$row['kelompok']]; ?></td>
                                    <td><?php echo $row['kkm']; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit.php?id=<?php echo $row['id_mapel']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="index.php?delete=<?php echo $row['id_mapel']; ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Yakin ingin menghapus mata pelajaran ini?')">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
