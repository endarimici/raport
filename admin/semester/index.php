<?php
require_once '../../config.php';
requireRole('administrator');

if (isset($_GET['delete'])) {
    $id = cleanInput($_GET['delete']);
    $query = "DELETE FROM semester WHERE id_semester = $id";
    if (mysqli_query($conn, $query)) {
        $success = "Semester berhasil dihapus!";
    } else {
        $error = "Gagal menghapus semester!";
    }
}

// Set semester aktif
if (isset($_GET['aktifkan'])) {
    $id = cleanInput($_GET['aktifkan']);
    // Set semua semester jadi nonaktif
    mysqli_query($conn, "UPDATE semester SET status = 'nonaktif'");
    // Set semester terpilih jadi aktif
    mysqli_query($conn, "UPDATE semester SET status = 'aktif' WHERE id_semester = $id");
    $success = "Semester berhasil diaktifkan!";
}

$query = "SELECT * FROM semester ORDER BY id_semester DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Semester - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Data Semester & Tahun Akademik</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Semester</h3>
                    <a href="add.php" class="btn btn-primary">Tambah Semester</a>
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
                                    <th>Nama Semester</th>
                                    <th>Semester</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Periode</th>
                                    <th>Status</th>
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
                                    <td><?php echo $row['nama_semester']; ?></td>
                                    <td><?php echo $row['semester']; ?></td>
                                    <td><?php echo $row['tahun_ajaran']; ?></td>
                                    <td>
                                        <?php echo formatTanggalIndo($row['tanggal_mulai']); ?> - 
                                        <?php echo formatTanggalIndo($row['tanggal_selesai']); ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $row['status'] == 'aktif' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($row['status'] == 'nonaktif'): ?>
                                                <a href="index.php?aktifkan=<?php echo $row['id_semester']; ?>" 
                                                   class="btn btn-success btn-sm">Aktifkan</a>
                                            <?php endif; ?>
                                            <a href="edit.php?id=<?php echo $row['id_semester']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="index.php?delete=<?php echo $row['id_semester']; ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Yakin ingin menghapus semester ini?')">Hapus</a>
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
