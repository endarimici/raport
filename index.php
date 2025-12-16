<?php
require_once 'config.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    if (hasRole('administrator')) {
        header("Location: admin/dashboard.php");
    } elseif (hasRole('wali_kelas')) {
        header("Location: wali_kelas/dashboard.php");
    } else {
        header("Location: guru/dashboard.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? cleanInput($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        // Query untuk cek user
        $query = "SELECT * FROM users WHERE username = '$username' AND status = 'aktif'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Verifikasi password
            // Support password_verify (bcrypt) atau MD5 untuk backward compatibility
            $password_valid = false;
            
            if (password_verify($password, $user['password'])) {
                $password_valid = true;
            } elseif (md5($password) == $user['password']) {
                // Support MD5 untuk backward compatibility
                $password_valid = true;
            } elseif ($password == $user['password']) {
                // Support plain text (TIDAK AMAN - hanya untuk testing)
                $password_valid = true;
            }
            
            if ($password_valid) {
                // Set session
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect berdasarkan role
                if ($user['role'] == 'administrator') {
                    header("Location: admin/dashboard.php");
                } elseif ($user['role'] == 'wali_kelas') {
                    header("Location: wali_kelas/dashboard.php");
                } else {
                    header("Location: guru/dashboard.php");
                }
                exit();
            } else {
                $error = 'Username atau password salah!';
            }
        } else {
            $error = 'Username atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1>Aplikasi Raport SMK</h1>
            <h2>Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            
        </div>
    </div>
</body>
</html>
