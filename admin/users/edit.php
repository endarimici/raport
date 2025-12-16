<?php
require_once '../../config.php';
requireRole('administrator');

$error = '';
$success = '';

// Ambil data user
$id = cleanInput($_GET['id']);
$query = "SELECT * FROM users WHERE id_user = $id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = cleanInput($_POST['username']);
    $nama_lengkap = cleanInput($_POST['nama_lengkap']);
    $role = cleanInput($_POST['role']);
    $status = cleanInput($_POST['status']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($nama_lengkap) || empty($role)) {
        $error = 'Username, nama lengkap, dan role harus diisi!';
    } else {
        // Cek username sudah ada atau belum (kecuali username sendiri)
        $check = "SELECT * FROM users WHERE username = '$username' AND id_user != $id";
        $result_check = mysqli_query($conn, $check);
        
        if (mysqli_num_rows($result_check) > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            // Update data
            if (!empty($password)) {
                // Jika password diisi, update dengan password baru
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query = "UPDATE users SET 
                          username = '$username',
                          password = '$hashed_password',
                          nama_lengkap = '$nama_lengkap',
                          role = '$role',
                          status = '$status'
                          WHERE id_user = $id";
            } else {
                // Jika password tidak diisi, update tanpa password
                $query = "UPDATE users SET 
                          username = '$username',
                          nama_lengkap = '$nama_lengkap',
                          role = '$role',
                          status = '$status'
                          WHERE id_user = $id";
            }
            
            if (mysqli_query($conn, $query)) {
                $success = 'User berhasil diupdate!';
                // Refresh data user
                $query = "SELECT * FROM users WHERE id_user = $id";
                $result = mysqli_query($conn, $query);
                $user = mysqli_fetch_assoc($result);
            } else {
                $error = 'Gagal mengupdate user!';
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
    <title>Edit User - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Edit User</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Form Edit User</h3>
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
                            <label for="username">Username *</label>
                            <input type="text" id="username" name="username" class="form-control" 
                                   value="<?php echo $user['username']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password (Kosongkan jika tidak diubah)</label>
                            <input type="password" id="password" name="password" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="nama_lengkap">Nama Lengkap *</label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" 
                                   value="<?php echo $user['nama_lengkap']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="role">Role *</label>
                            <select id="role" name="role" class="form-control" required>
                                <option value="administrator" <?php echo $user['role'] == 'administrator' ? 'selected' : ''; ?>>Administrator</option>
                                <option value="guru" <?php echo $user['role'] == 'guru' ? 'selected' : ''; ?>>Guru</option>
                                <option value="wali_kelas" <?php echo $user['role'] == 'wali_kelas' ? 'selected' : ''; ?>>Wali Kelas</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="aktif" <?php echo $user['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="nonaktif" <?php echo $user['status'] == 'nonaktif' ? 'selected' : ''; ?>>Non Aktif</option>
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
