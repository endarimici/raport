<?php
require_once '../../config.php';
requireRole('administrator');

$error = '';
$success = '';

$id = cleanInput($_GET['id']);
$query = "SELECT * FROM mata_pelajaran WHERE id_mapel = $id";
$result = mysqli_query($conn, $query);
$mapel = mysqli_fetch_assoc($result);

if (!$mapel) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_mapel = cleanInput($_POST['kode_mapel']);
    $nama_mapel = cleanInput($_POST['nama_mapel']);
    $kelompok = cleanInput($_POST['kelompok']);
    $kkm = cleanInput($_POST['kkm']);
    
    if (empty($kode_mapel) || empty($nama_mapel) || empty($kelompok)) {
        $error = 'Kode, nama, dan kelompok mata pelajaran harus diisi!';
    } else {
        $check = "SELECT * FROM mata_pelajaran WHERE kode_mapel = '$kode_mapel' AND id_mapel != $id";
        $result_check = mysqli_query($conn, $check);
        
        if (mysqli_num_rows($result_check) > 0) {
            $error = 'Kode mata pelajaran sudah digunakan!';
        } else {
            $query = "UPDATE mata_pelajaran SET 
                      kode_mapel = '$kode_mapel',
                      nama_mapel = '$nama_mapel',
                      kelompok = '$kelompok',
                      kkm = '$kkm'
                      WHERE id_mapel = $id";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Mata pelajaran berhasil diupdate!';
                $query = "SELECT * FROM mata_pelajaran WHERE id_mapel = $id";
                $result = mysqli_query($conn, $query);
                $mapel = mysqli_fetch_assoc($result);
            } else {
                $error = 'Gagal mengupdate mata pelajaran!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mata Pelajaran - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Edit Mata Pelajaran</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Form Edit Mata Pelajaran</h3>
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="kode_mapel">Kode Mata Pelajaran *</label>
                            <input type="text" id="kode_mapel" name="kode_mapel" class="form-control" 
                                   value="<?php echo $mapel['kode_mapel']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nama_mapel">Nama Mata Pelajaran *</label>
                            <input type="text" id="nama_mapel" name="nama_mapel" class="form-control" 
                                   value="<?php echo $mapel['nama_mapel']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="kelompok">Kelompok *</label>
                            <select id="kelompok" name="kelompok" class="form-control" required>
                                <option value="A" <?php echo $mapel['kelompok'] == 'A' ? 'selected' : ''; ?>>A - Umum</option>
                                <option value="B" <?php echo $mapel['kelompok'] == 'B' ? 'selected' : ''; ?>>B - Kejuruan</option>
                                <option value="C" <?php echo $mapel['kelompok'] == 'C' ? 'selected' : ''; ?>>C - Muatan Lokal</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="kkm">KKM *</label>
                            <input type="number" id="kkm" name="kkm" class="form-control" 
                                   value="<?php echo $mapel['kkm']; ?>" step="0.01" min="0" max="100" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
