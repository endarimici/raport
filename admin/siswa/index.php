<?php
require_once '../../config.php';
requireRole('administrator');

if (isset($_GET['delete'])) {
    $id = cleanInput($_GET['delete']);
    $query = "DELETE FROM siswa WHERE id_siswa = $id";
    if (mysqli_query($conn, $query)) {
        $success = "Siswa berhasil dihapus!";
    } else {
        $error = "Gagal menghapus siswa!";
    }
}

// Filter dan Sort
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$filterRombel = isset($_GET['rombel']) ? cleanInput($_GET['rombel']) : '';
$sort = isset($_GET['sort']) ? cleanInput($_GET['sort']) : 'id_siswa';
$order = isset($_GET['order']) ? cleanInput($_GET['order']) : 'DESC';

// Ambil data rombel untuk dropdown
$rombelQuery = "SELECT id_rombel, nama_rombel FROM rombel ORDER BY nama_rombel ASC";
$rombelResult = mysqli_query($conn, $rombelQuery);

// Validasi kolom sort
$allowedSort = ['id_siswa', 'nis', 'nama_lengkap', 'jenis_kelamin', 'nama_rombel', 'status'];
if (!in_array($sort, $allowedSort)) {
    $sort = 'id_siswa';
}

// Validasi order
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

// Query dengan filter dan sort
$query = "SELECT s.*, r.nama_rombel 
          FROM siswa s 
          LEFT JOIN rombel r ON s.id_rombel = r.id_rombel 
          WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (s.nis LIKE '%$search%' OR s.nama_lengkap LIKE '%$search%')";
}

if (!empty($filterRombel)) {
    $query .= " AND s.id_rombel = $filterRombel";
}

$query .= " ORDER BY " . ($sort === 'nama_rombel' ? 'r.nama_rombel' : 's.' . $sort) . " $order";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Data Siswa</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Siswa</h3>
                    <a href="add.php" class="btn btn-primary">Tambah Siswa</a>
                </div>
                <div class="card-body">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <!-- Filter Form -->
                    <div class="filter-section" style="margin-bottom: 20px;">
                        <form method="GET" action="" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <input type="text" name="search" placeholder="Cari NIS atau Nama..." 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; flex: 1; max-width: 300px;">
                            <select name="rombel" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; min-width: 150px;">
                                <option value="">Semua Rombel</option>
                                <?php while ($rombel = mysqli_fetch_assoc($rombelResult)): ?>
                                    <option value="<?php echo $rombel['id_rombel']; ?>" 
                                            <?php echo $filterRombel == $rombel['id_rombel'] ? 'selected' : ''; ?>>
                                        <?php echo $rombel['nama_rombel']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">🔍 Cari</button>
                            <?php if (!empty($search) || !empty($filterRombel)): ?>
                                <a href="index.php" class="btn btn-secondary btn-sm">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>
                                        <a href="?search=<?php echo urlencode($search); ?>&rombel=<?php echo urlencode($filterRombel); ?>&sort=nis&order=<?php echo ($sort == 'nis' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>" style="color: inherit; text-decoration: none;">
                                            NIS <?php if($sort == 'nis') echo $order == 'ASC' ? '▲' : '▼'; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?search=<?php echo urlencode($search); ?>&rombel=<?php echo urlencode($filterRombel); ?>&sort=nama_lengkap&order=<?php echo ($sort == 'nama_lengkap' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>" style="color: inherit; text-decoration: none;">
                                            Nama Lengkap <?php if($sort == 'nama_lengkap') echo $order == 'ASC' ? '▲' : '▼'; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?search=<?php echo urlencode($search); ?>&rombel=<?php echo urlencode($filterRombel); ?>&sort=jenis_kelamin&order=<?php echo ($sort == 'jenis_kelamin' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>" style="color: inherit; text-decoration: none;">
                                            JK <?php if($sort == 'jenis_kelamin') echo $order == 'ASC' ? '▲' : '▼'; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?search=<?php echo urlencode($search); ?>&rombel=<?php echo urlencode($filterRombel); ?>&sort=nama_rombel&order=<?php echo ($sort == 'nama_rombel' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>" style="color: inherit; text-decoration: none;">
                                            Rombel <?php if($sort == 'nama_rombel') echo $order == 'ASC' ? '▲' : '▼'; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?search=<?php echo urlencode($search); ?>&rombel=<?php echo urlencode($filterRombel); ?>&sort=status&order=<?php echo ($sort == 'status' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>" style="color: inherit; text-decoration: none;">
                                            Status <?php if($sort == 'status') echo $order == 'ASC' ? '▲' : '▼'; ?>
                                        </a>
                                    </th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                if (mysqli_num_rows($result) > 0):
                                    while ($row = mysqli_fetch_assoc($result)): 
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo $row['nis']; ?></td>
                                    <td><?php echo $row['nama_lengkap']; ?></td>
                                    <td><?php echo $row['jenis_kelamin']; ?></td>
                                    <td><?php echo $row['nama_rombel']; ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $row['status'] == 'aktif' ? 'success' : 
                                                ($row['status'] == 'lulus' ? 'info' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit.php?id=<?php echo $row['id_siswa']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="index.php?delete=<?php echo $row['id_siswa']; ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Yakin ingin menghapus siswa ini?')">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else: 
                                ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px;">
                                        <?php echo !empty($search) ? 'Tidak ada data siswa yang sesuai dengan pencarian.' : 'Belum ada data siswa.'; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
