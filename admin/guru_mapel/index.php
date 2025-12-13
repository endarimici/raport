<?php
require_once '../../config.php';
requireRole('administrator');

// Hapus mapping
if (isset($_GET['delete'])) {
    $id = cleanInput($_GET['delete']);
    $query = "DELETE FROM mapel_guru WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        $success = "Mapping guru-mapel berhasil dihapus!";
    } else {
        $error = "Gagal menghapus mapping!";
    }
}

// Filter dan Sort
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$filterSemester = isset($_GET['semester']) ? cleanInput($_GET['semester']) : '';
$sort = isset($_GET['sort']) ? cleanInput($_GET['sort']) : 'id';
$order = isset($_GET['order']) ? cleanInput($_GET['order']) : 'DESC';

// Ambil data semester untuk dropdown
$semesterQuery = "SELECT id_semester, nama_semester FROM semester ORDER BY nama_semester DESC";
$semesterResult = mysqli_query($conn, $semesterQuery);

// Validasi kolom sort
$allowedSort = ['id', 'nama_lengkap', 'nama_mapel', 'nama_rombel', 'nama_semester'];
if (!in_array($sort, $allowedSort)) {
    $sort = 'id';
}

// Validasi order
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

// Query dengan JOIN
$query = "SELECT mg.*, 
          u.nama_lengkap, 
          m.nama_mapel, 
          r.nama_rombel, 
          s.nama_semester
          FROM mapel_guru mg
          INNER JOIN users u ON mg.id_user = u.id_user
          INNER JOIN mata_pelajaran m ON mg.id_mapel = m.id_mapel
          INNER JOIN rombel r ON mg.id_rombel = r.id_rombel
          INNER JOIN semester s ON mg.id_semester = s.id_semester
          WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (u.nama_lengkap LIKE '%$search%' OR m.nama_mapel LIKE '%$search%' OR r.nama_rombel LIKE '%$search%')";
}

if (!empty($filterSemester)) {
    $query .= " AND mg.id_semester = $filterSemester";
}

$query .= " ORDER BY ";
if ($sort === 'nama_lengkap') {
    $query .= "u.nama_lengkap";
} elseif ($sort === 'nama_mapel') {
    $query .= "m.nama_mapel";
} elseif ($sort === 'nama_rombel') {
    $query .= "r.nama_rombel";
} elseif ($sort === 'nama_semester') {
    $query .= "s.nama_semester";
} else {
    $query .= "mg.$sort";
}
$query .= " $order";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapping Guru & Mata Pelajaran - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Mapping Guru & Mata Pelajaran</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Mapping Guru-Mapel-Rombel</h3>
                    <a href="add.php" class="btn btn-primary">Tambah Mapping</a>
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
                            <input type="text" name="search" placeholder="Cari Guru, Mapel, atau Rombel..." 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; flex: 1; max-width: 300px;">
                            <select name="semester" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; min-width: 150px;">
                                <option value="">Semua Semester</option>
                                <?php while ($sem = mysqli_fetch_assoc($semesterResult)): ?>
                                    <option value="<?php echo $sem['id_semester']; ?>" 
                                            <?php echo $filterSemester == $sem['id_semester'] ? 'selected' : ''; ?>>
                                        <?php echo $sem['nama_semester']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">🔍 Cari</button>
                            <?php if (!empty($search) || !empty($filterSemester)): ?>
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
                                        <a href="?search=<?php echo urlencode($search); ?>&semester=<?php echo urlencode($filterSemester); ?>&sort=nama_lengkap&order=<?php echo ($sort == 'nama_lengkap' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>" style="color: inherit; text-decoration: none;">
                                            Nama Guru <?php if($sort == 'nama_lengkap') echo $order == 'ASC' ? '▲' : '▼'; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?search=<?php echo urlencode($search); ?>&semester=<?php echo urlencode($filterSemester); ?>&sort=nama_mapel&order=<?php echo ($sort == 'nama_mapel' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>" style="color: inherit; text-decoration: none;">
                                            Mata Pelajaran <?php if($sort == 'nama_mapel') echo $order == 'ASC' ? '▲' : '▼'; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?search=<?php echo urlencode($search); ?>&semester=<?php echo urlencode($filterSemester); ?>&sort=nama_rombel&order=<?php echo ($sort == 'nama_rombel' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>" style="color: inherit; text-decoration: none;">
                                            Rombel <?php if($sort == 'nama_rombel') echo $order == 'ASC' ? '▲' : '▼'; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?search=<?php echo urlencode($search); ?>&semester=<?php echo urlencode($filterSemester); ?>&sort=nama_semester&order=<?php echo ($sort == 'nama_semester' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>" style="color: inherit; text-decoration: none;">
                                            Semester <?php if($sort == 'nama_semester') echo $order == 'ASC' ? '▲' : '▼'; ?>
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
                                    <td><?php echo $row['nama_lengkap']; ?></td>
                                    <td><?php echo $row['nama_mapel']; ?></td>
                                    <td><?php echo $row['nama_rombel']; ?></td>
                                    <td><?php echo $row['nama_semester']; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="index.php?delete=<?php echo $row['id']; ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Yakin ingin menghapus mapping ini?')">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else: 
                                ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 20px;">
                                        <?php echo !empty($search) || !empty($filterSemester) ? 'Tidak ada data yang sesuai dengan filter.' : 'Belum ada data mapping.'; ?>
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
