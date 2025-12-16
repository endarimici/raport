<?php
/**
 * Script untuk update database menambahkan role wali_kelas
 * Jalankan file ini sekali saja di browser: http://localhost/raport/update_role_wali_kelas.php
 * Setelah berhasil, hapus file ini untuk keamanan
 */

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'raport_smk';

// Connect to database
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check connection
if (!$conn) {
    die("❌ Koneksi database gagal: " . mysqli_connect_error());
}

echo "<h2>Update Database - Tambah Role Wali Kelas</h2>";
echo "<hr>";

// Update table users - modify role column
$sql = "ALTER TABLE users MODIFY COLUMN role ENUM('administrator', 'guru', 'wali_kelas') NOT NULL";

if (mysqli_query($conn, $sql)) {
    echo "✅ <strong>Berhasil!</strong> Role 'wali_kelas' telah ditambahkan ke database.<br><br>";
    
    echo "<h3>Langkah Selanjutnya:</h3>";
    echo "<ol>";
    echo "<li>Login sebagai <strong>admin</strong></li>";
    echo "<li>Buat user baru dengan role <strong>Wali Kelas</strong></li>";
    echo "<li>Logout dan login dengan user wali kelas</li>";
    echo "<li>Cek dashboard dan fitur download raport</li>";
    echo "</ol>";
    
    echo "<h3>Optional: Tambah Sample User Wali Kelas</h3>";
    echo "<p>Klik tombol di bawah untuk menambahkan sample user wali kelas:</p>";
    echo "<form method='post' style='margin: 10px 0;'>";
    echo "<button type='submit' name='add_sample' style='padding: 10px 20px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 5px;'>➕ Tambah Sample User (username: wali1, password: wali123)</button>";
    echo "</form>";
    
    if (isset($_POST['add_sample'])) {
        // Insert sample wali kelas user
        $sql_sample = "INSERT INTO users (username, password, nama_lengkap, role, status) VALUES 
                       ('wali1', '$2y$10\$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Wali Kelas 1, S.Pd', 'wali_kelas', 'aktif')";
        
        if (mysqli_query($conn, $sql_sample)) {
            echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb; border-radius: 5px;'>";
            echo "✅ Sample user wali kelas berhasil ditambahkan!<br>";
            echo "<strong>Username:</strong> wali1<br>";
            echo "<strong>Password:</strong> wali123<br>";
            echo "<strong>Nama:</strong> Wali Kelas 1, S.Pd";
            echo "</div>";
        } else {
            if (mysqli_errno($conn) == 1062) {
                echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border: 1px solid #ffeeba; border-radius: 5px;'>";
                echo "⚠️ User 'wali1' sudah ada di database.";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border: 1px solid #f5c6cb; border-radius: 5px;'>";
                echo "❌ Gagal menambahkan sample user: " . mysqli_error($conn);
                echo "</div>";
            }
        }
    }
    
    echo "<hr>";
    echo "<h3>⚠️ PENTING</h3>";
    echo "<p style='color: red;'><strong>Setelah update berhasil, hapus file ini (update_role_wali_kelas.php) untuk keamanan!</strong></p>";
    
} else {
    // Check if already updated
    if (mysqli_errno($conn) == 1060 || mysqli_errno($conn) == 1291) {
        echo "ℹ️ <strong>Informasi:</strong> Role 'wali_kelas' sudah ada di database.<br>";
        echo "Database sudah dalam kondisi terbaru.<br><br>";
        
        echo "<h3>Optional: Tambah Sample User Wali Kelas</h3>";
        echo "<p>Klik tombol di bawah untuk menambahkan sample user wali kelas:</p>";
        echo "<form method='post' style='margin: 10px 0;'>";
        echo "<button type='submit' name='add_sample' style='padding: 10px 20px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 5px;'>➕ Tambah Sample User (username: wali1, password: wali123)</button>";
        echo "</form>";
        
        if (isset($_POST['add_sample'])) {
            // Insert sample wali kelas user
            $sql_sample = "INSERT INTO users (username, password, nama_lengkap, role, status) VALUES 
                           ('wali1', '$2y$10\$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Wali Kelas 1, S.Pd', 'wali_kelas', 'aktif')";
            
            if (mysqli_query($conn, $sql_sample)) {
                echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb; border-radius: 5px;'>";
                echo "✅ Sample user wali kelas berhasil ditambahkan!<br>";
                echo "<strong>Username:</strong> wali1<br>";
                echo "<strong>Password:</strong> wali123<br>";
                echo "<strong>Nama:</strong> Wali Kelas 1, S.Pd";
                echo "</div>";
            } else {
                if (mysqli_errno($conn) == 1062) {
                    echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border: 1px solid #ffeeba; border-radius: 5px;'>";
                    echo "⚠️ User 'wali1' sudah ada di database.";
                    echo "</div>";
                } else {
                    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border: 1px solid #f5c6cb; border-radius: 5px;'>";
                    echo "❌ Gagal menambahkan sample user: " . mysqli_error($conn);
                    echo "</div>";
                }
            }
        }
        
        echo "<hr>";
        echo "<p style='color: red;'><strong>Hapus file ini (update_role_wali_kelas.php) untuk keamanan!</strong></p>";
    } else {
        echo "❌ <strong>Error:</strong> Gagal mengupdate database.<br>";
        echo "Error: " . mysqli_error($conn) . "<br>";
        echo "Error Code: " . mysqli_errno($conn) . "<br><br>";
        echo "<p>Silakan coba update manual melalui phpMyAdmin dengan query:</p>";
        echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ddd;'>ALTER TABLE users MODIFY COLUMN role ENUM('administrator', 'guru', 'wali_kelas') NOT NULL;</pre>";
    }
}

mysqli_close($conn);

echo "<hr>";
echo "<p><a href='index.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>🏠 Kembali ke Halaman Login</a></p>";
?>
