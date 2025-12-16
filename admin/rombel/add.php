<?php
require_once '../../config.php';
requireRole('administrator');

$error = '';
$success = '';

// Ambil data jurusan
$query_jurusan = "SELECT * FROM jurusan ORDER BY nama_jurusan";
$result_jurusan = mysqli_query($conn, $query_jurusan);

// Ambil data wali kelas (users dengan role wali_kelas)
$query_wali = "SELECT id_user, nama_lengkap FROM users WHERE role = 'wali_kelas' AND status = 'aktif' ORDER BY nama_lengkap";
$result_wali = mysqli_query($conn, $query_wali);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_rombel = cleanInput($_POST['nama_rombel']);
    $id_jurusan = cleanInput($_POST['id_jurusan']);
    $tingkat = cleanInput($_POST['tingkat']);
    $id_wali_kelas = !empty($_POST['id_wali_kelas']) ? cleanInput($_POST['id_wali_kelas']) : 'NULL';
    $tahun_ajaran = cleanInput($_POST['tahun_ajaran']);
    
    if (empty($nama_rombel) || empty($id_jurusan) || empty($tingkat)) {
        $error = 'Nama rombel, jurusan, dan tingkat harus diisi!';
    } else {
        $query = "INSERT INTO rombel (nama_rombel, id_jurusan, tingkat, id_wali_kelas, tahun_ajaran) 
                  VALUES ('$nama_rombel', '$id_jurusan', '$tingkat', $id_wali_kelas, '$tahun_ajaran')";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Rombel berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan rombel!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Rombel - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Tambah Rombongan Belajar</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Form Tambah Rombel</h3>
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
                            <label for="nama_rombel">Nama Rombel *</label>
                            <input type="text" id="nama_rombel" name="nama_rombel" class="form-control" 
                                   placeholder="Contoh: X TKJ 1" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_jurusan">Jurusan *</label>
                            <select id="id_jurusan" name="id_jurusan" class="form-control" required>
                                <option value="">-- Pilih Jurusan --</option>
                                <?php while ($jurusan = mysqli_fetch_assoc($result_jurusan)): ?>
                                    <option value="<?php echo $jurusan['id_jurusan']; ?>">
                                        <?php echo $jurusan['nama_jurusan']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="tingkat">Tingkat *</label>
                            <select id="tingkat" name="tingkat" class="form-control" required>
                                <option value="">-- Pilih Tingkat --</option>
                                <option value="X">X</option>
                                <option value="XI">XI</option>
                                <option value="XII">XII</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_wali_kelas">Wali Kelas</label>
                            <select id="id_wali_kelas" name="id_wali_kelas" class="form-control">
                                <option value="">-- Pilih Wali Kelas --</option>
                                <?php while ($wali = mysqli_fetch_assoc($result_wali)): ?>
                                    <option value="<?php echo $wali['id_user']; ?>">
                                        <?php echo $wali['nama_lengkap']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="tahun_ajaran">Tahun Ajaran</label>
                            <input type="text" id="tahun_ajaran" name="tahun_ajaran" class="form-control" 
                                   placeholder="Contoh: 2024/2025">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
