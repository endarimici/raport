<?php
require_once '../../config.php';
requireRole('administrator');

// Ambil data untuk dropdown
$queryGuru = "SELECT id_user, nama_lengkap, username FROM users WHERE role = 'guru' ORDER BY nama_lengkap";
$resultGuru = mysqli_query($conn, $queryGuru);

$queryMapel = "SELECT id_mapel, nama_mapel FROM mata_pelajaran ORDER BY nama_mapel";
$resultMapel = mysqli_query($conn, $queryMapel);

$queryRombel = "SELECT id_rombel, nama_rombel FROM rombel ORDER BY nama_rombel";
$resultRombel = mysqli_query($conn, $queryRombel);

$querySemester = "SELECT id_semester, nama_semester FROM semester ORDER BY nama_semester DESC";
$resultSemester = mysqli_query($conn, $querySemester);

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user = cleanInput($_POST['id_user']);
    $id_mapel = cleanInput($_POST['id_mapel']);
    $id_rombel = cleanInput($_POST['id_rombel']);
    $id_semester = cleanInput($_POST['id_semester']);
    
    // Cek apakah mapping sudah ada
    $checkQuery = "SELECT * FROM mapel_guru 
                   WHERE id_user = $id_user 
                   AND id_mapel = '$id_mapel' 
                   AND id_rombel = $id_rombel 
                   AND id_semester = $id_semester";
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if (mysqli_num_rows($checkResult) > 0) {
        $error = "Mapping guru-mapel-rombel untuk semester ini sudah ada!";
    } else {
        $query = "INSERT INTO mapel_guru (id_user, id_mapel, id_rombel, id_semester) 
                  VALUES ($id_user, '$id_mapel', $id_rombel, $id_semester)";
        
        if (mysqli_query($conn, $query)) {
            header("Location: index.php");
            exit();
        } else {
            $error = "Gagal menambahkan mapping: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mapping Guru-Mapel - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Tambah Mapping Guru & Mata Pelajaran</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Form Tambah Mapping</h3>
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="id_user">Guru *</label>
                            <select id="id_user" name="id_user" class="form-control" required>
                                <option value="">-- Pilih Guru --</option>
                                <?php while ($guru = mysqli_fetch_assoc($resultGuru)): ?>
                                    <option value="<?php echo $guru['id_user']; ?>" <?php echo isset($_POST['id_user']) && $_POST['id_user'] == $guru['id_user'] ? 'selected' : ''; ?>>
                                        <?php echo $guru['nama_lengkap']; ?> (<?php echo $guru['username']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_mapel">Mata Pelajaran *</label>
                            <select id="id_mapel" name="id_mapel" class="form-control" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                <?php while ($mapel = mysqli_fetch_assoc($resultMapel)): ?>
                                    <option value="<?php echo $mapel['id_mapel']; ?>" <?php echo isset($_POST['id_mapel']) && $_POST['id_mapel'] == $mapel['id_mapel'] ? 'selected' : ''; ?>>
                                        <?php echo $mapel['nama_mapel']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_rombel">Rombel *</label>
                            <select id="id_rombel" name="id_rombel" class="form-control" required>
                                <option value="">-- Pilih Rombel --</option>
                                <?php while ($rombel = mysqli_fetch_assoc($resultRombel)): ?>
                                    <option value="<?php echo $rombel['id_rombel']; ?>" <?php echo isset($_POST['id_rombel']) && $_POST['id_rombel'] == $rombel['id_rombel'] ? 'selected' : ''; ?>>
                                        <?php echo $rombel['nama_rombel']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_semester">Semester *</label>
                            <select id="id_semester" name="id_semester" class="form-control" required>
                                <option value="">-- Pilih Semester --</option>
                                <?php while ($semester = mysqli_fetch_assoc($resultSemester)): ?>
                                    <option value="<?php echo $semester['id_semester']; ?>" <?php echo isset($_POST['id_semester']) && $_POST['id_semester'] == $semester['id_semester'] ? 'selected' : ''; ?>>
                                        <?php echo $semester['nama_semester']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Info:</strong> Mapping ini akan menentukan guru mana yang mengajar mata pelajaran apa, di rombel mana, dan pada semester berapa.
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
