<?php
require_once '../../config.php';
requireRole('wali_kelas');

// Ambil ID wali kelas dari session
$id_wali_kelas = $_SESSION['user_id'];

// Filter
$filter_rombel = isset($_GET['rombel']) ? cleanInput($_GET['rombel']) : '';
$filter_semester = isset($_GET['semester']) ? cleanInput($_GET['semester']) : '';

// Ambil semua rombel yang di-handle wali kelas ini
$query_rombel = "SELECT * FROM rombel WHERE id_wali_kelas = '$id_wali_kelas' ORDER BY nama_rombel";

$result_rombel_list = mysqli_query($conn, $query_rombel);

// Cek apakah wali kelas punya rombel
if(mysqli_num_rows($result_rombel_list) == 0) {
    die("Anda belum ditugaskan sebagai wali kelas untuk rombel manapun.");
}

// Jika filter rombel tidak dipilih, ambil rombel pertama sebagai default
if(empty($filter_rombel)) {
    $result_rombel_list_temp = mysqli_query($conn, $query_rombel);
    $first_rombel = mysqli_fetch_assoc($result_rombel_list_temp);
    $filter_rombel = $first_rombel['id_rombel'];
}

// Query semester
$query_semester = "SELECT * FROM semester ORDER BY id_semester DESC";
$result_semester_filter = mysqli_query($conn, $query_semester);

// Query rombel untuk dropdown filter
$result_rombel_filter = mysqli_query($conn, $query_rombel);

// Query siswa di rombel yang dipilih
$query = "SELECT s.*, r.nama_rombel, j.nama_jurusan,
          (SELECT nama_lengkap FROM users WHERE id_user = r.id_wali_kelas) as wali_kelas
          FROM siswa s
          INNER JOIN rombel r ON s.id_rombel = r.id_rombel
          INNER JOIN jurusan j ON r.id_jurusan = j.id_jurusan
          WHERE s.id_rombel = '$filter_rombel'
          ORDER BY s.nama_lengkap";
$result = mysqli_query($conn, $query);

// Ambil nama rombel yang sedang dipilih
$query_current_rombel = "SELECT nama_rombel FROM rombel WHERE id_rombel = '$filter_rombel'";
$result_current_rombel = mysqli_query($conn, $query_current_rombel);
$current_rombel = mysqli_fetch_assoc($result_current_rombel);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nilai Rapor - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-preview {
            background-color: #2196F3;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 12px;
        }
        .btn-review {
            background-color: #FF9800;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 12px;
        }
        .btn-preview:hover {
            background-color: #1976D2;
        }
        .btn-review:hover {
            background-color: #F57C00;
        }
        .info-box {
            background-color: #E3F2FD;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Nilai Rapor Siswa</h2>
                <div class="user-info">
                    <span>👤 <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?> (<?php echo ucfirst($_SESSION['role']); ?>)</span>
                    <a href="<?php echo BASE_URL; ?>logout.php" class="btn-logout">Logout</a>
                </div>
            </div>
            
            <div class="content">
                <div class="card">
                    <div class="card-header">
                        <h3>Filter Data</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="" class="filter-form">
                            <div class="form-group">
                                <label>Rombel:</label>
                                <select name="rombel" class="form-control" required>
                                    <option value="">-- Pilih Rombel --</option>
                                    <?php while($row = mysqli_fetch_assoc($result_rombel_filter)): ?>
                                    <option value="<?php echo $row['id_rombel']; ?>" <?php echo $filter_rombel == $row['id_rombel'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['nama_rombel']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Semester:</label>
                                <select name="semester" class="form-control">
                                    <option value="">-- Pilih Semester --</option>
                                    <?php while($row = mysqli_fetch_assoc($result_semester_filter)): ?>
                                    <option value="<?php echo $row['id_semester']; ?>" <?php echo $filter_semester == $row['id_semester'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['nama_semester']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </form>
                    </div>
                </div>

                <div class="card" style="margin-top: 20px;">
                    <div class="card-header">
                        <h3>Data Siswa - <?php echo htmlspecialchars($current_rombel['nama_rombel']); ?></h3>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Rombel</th>
                                    <th>Jurusan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                if(mysqli_num_rows($result) > 0):
                                    while($row = mysqli_fetch_assoc($result)): 
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_rombel']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_jurusan']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="preview.php?id=<?php echo $row['id_siswa']; ?><?php echo $filter_semester ? '&semester='.$filter_semester : ''; ?>" 
                                               class="btn-preview" target="_blank" title="Preview Rapor">
                                                📄 Preview Rapor
                                            </a>
                                            <a href="review.php?id=<?php echo $row['id_siswa']; ?><?php echo $filter_semester ? '&semester='.$filter_semester : ''; ?>" 
                                               class="btn-review" title="Review Rapor">
                                                ✏️ Review Rapor
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">Tidak ada data siswa</td>
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
