<?php
require_once '../../config.php';
requireRole('administrator');

if (isset($_GET['delete'])) {
    $id = cleanInput($_GET['delete']);
    $query = "DELETE FROM rombel WHERE id_rombel = $id";
    if (mysqli_query($conn, $query)) {
        $success = "Rombel berhasil dihapus!";
    } else {
        $error = "Gagal menghapus rombel!";
    }
}

$query = "SELECT r.*, j.nama_jurusan, u.nama_lengkap as nama_wali_kelas 
          FROM rombel r 
          LEFT JOIN jurusan j ON r.id_jurusan = j.id_jurusan 
          LEFT JOIN users u ON r.id_wali_kelas = u.id_user
          ORDER BY r.id_rombel DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Rombel - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Data Rombongan Belajar</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Rombongan Belajar</h3>
                    <a href="add.php" class="btn btn-primary">Tambah Rombel</a>
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
                                    <th>Nama Rombel</th>
                                    <th>Jurusan</th>
                                    <th>Tingkat</th>
                                    <th>Wali Kelas</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)): 
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo $row['nama_rombel']; ?></td>
                                    <td><?php echo $row['nama_jurusan']; ?></td>
                                    <td><?php echo $row['tingkat']; ?></td>
                                    <td><?php echo $row['nama_wali_kelas'] ? $row['nama_wali_kelas'] : '-'; ?></td>
                                    <td><?php echo $row['tahun_ajaran']; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit.php?id=<?php echo $row['id_rombel']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="index.php?delete=<?php echo $row['id_rombel']; ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Yakin ingin menghapus rombel ini?')">Hapus</a>
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
