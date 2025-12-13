<?php
require_once '../../config.php';
requireRole('administrator');

$error = '';
$success = '';

$id = cleanInput($_GET['id']);
$query = "SELECT * FROM siswa WHERE id_siswa = $id";
$result = mysqli_query($conn, $query);
$siswa = mysqli_fetch_assoc($result);

if (!$siswa) {
    header("Location: index.php");
    exit();
}

// Ambil data rombel
$query_rombel = "SELECT * FROM rombel ORDER BY nama_rombel";
$result_rombel = mysqli_query($conn, $query_rombel);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nis = cleanInput($_POST['nis']);
    $nisn = cleanInput($_POST['nisn']);
    $nama_lengkap = cleanInput($_POST['nama_lengkap']);
    $jenis_kelamin = cleanInput($_POST['jenis_kelamin']);
    $tempat_lahir = cleanInput($_POST['tempat_lahir']);
    $tanggal_lahir = cleanInput($_POST['tanggal_lahir']);
    $alamat = cleanInput($_POST['alamat']);
    $telepon = cleanInput($_POST['telepon']);
    $id_rombel = cleanInput($_POST['id_rombel']);
    $status = cleanInput($_POST['status']);
    
    if (empty($nis) || empty($nama_lengkap) || empty($jenis_kelamin)) {
        $error = 'NIS, nama lengkap, dan jenis kelamin harus diisi!';
    } else {
        $check = "SELECT * FROM siswa WHERE nis = '$nis' AND id_siswa != $id";
        $result_check = mysqli_query($conn, $check);
        
        if (mysqli_num_rows($result_check) > 0) {
            $error = 'NIS sudah digunakan!';
        } else {
            $query = "UPDATE siswa SET 
                      nis = '$nis',
                      nisn = '$nisn',
                      nama_lengkap = '$nama_lengkap',
                      jenis_kelamin = '$jenis_kelamin',
                      tempat_lahir = '$tempat_lahir',
                      tanggal_lahir = '$tanggal_lahir',
                      alamat = '$alamat',
                      telepon = '$telepon',
                      id_rombel = " . ($id_rombel ? "'$id_rombel'" : "NULL") . ",
                      status = '$status'
                      WHERE id_siswa = $id";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Siswa berhasil diupdate!';
                $query = "SELECT * FROM siswa WHERE id_siswa = $id";
                $result = mysqli_query($conn, $query);
                $siswa = mysqli_fetch_assoc($result);
            } else {
                $error = 'Gagal mengupdate siswa!';
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
    <title>Edit Siswa - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Edit Siswa</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Form Edit Siswa</h3>
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
                            <label for="nis">NIS *</label>
                            <input type="text" id="nis" name="nis" class="form-control" 
                                   value="<?php echo $siswa['nis']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nisn">NISN</label>
                            <input type="text" id="nisn" name="nisn" class="form-control" 
                                   value="<?php echo $siswa['nisn']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="nama_lengkap">Nama Lengkap *</label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" 
                                   value="<?php echo $siswa['nama_lengkap']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="jenis_kelamin">Jenis Kelamin *</label>
                            <select id="jenis_kelamin" name="jenis_kelamin" class="form-control" required>
                                <option value="L" <?php echo $siswa['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="P" <?php echo $siswa['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="tempat_lahir">Tempat Lahir</label>
                            <input type="text" id="tempat_lahir" name="tempat_lahir" class="form-control" 
                                   value="<?php echo $siswa['tempat_lahir']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal_lahir">Tanggal Lahir</label>
                            <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control" 
                                   value="<?php echo $siswa['tanggal_lahir']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <textarea id="alamat" name="alamat" class="form-control"><?php echo $siswa['alamat']; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="telepon">Telepon</label>
                            <input type="text" id="telepon" name="telepon" class="form-control" 
                                   value="<?php echo $siswa['telepon']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="id_rombel">Rombel</label>
                            <select id="id_rombel" name="id_rombel" class="form-control">
                                <option value="">-- Pilih Rombel --</option>
                                <?php while ($rombel = mysqli_fetch_assoc($result_rombel)): ?>
                                    <option value="<?php echo $rombel['id_rombel']; ?>" 
                                        <?php echo $rombel['id_rombel'] == $siswa['id_rombel'] ? 'selected' : ''; ?>>
                                        <?php echo $rombel['nama_rombel']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="aktif" <?php echo $siswa['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="lulus" <?php echo $siswa['status'] == 'lulus' ? 'selected' : ''; ?>>Lulus</option>
                                <option value="keluar" <?php echo $siswa['status'] == 'keluar' ? 'selected' : ''; ?>>Keluar</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
