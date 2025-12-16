<?php
/**
 * Script untuk update database - Relasi Wali Kelas dengan Users
 * Jalankan file ini sekali via browser, lalu hapus file ini setelah selesai
 * URL: http://localhost/raport/update_rombel_wali.php
 */

// Include config
require_once 'config.php';

// Cek apakah sudah dijalankan
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM rombel LIKE 'id_wali_kelas'");
$already_updated = mysqli_num_rows($check_column) > 0;

$success = [];
$errors = [];

// Proses update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    
    // Step 1: Tambah kolom id_wali_kelas
    if (!$already_updated) {
        $sql1 = "ALTER TABLE rombel ADD COLUMN id_wali_kelas INT(11) NULL AFTER wali_kelas";
        if (mysqli_query($conn, $sql1)) {
            $success[] = "✓ Kolom id_wali_kelas berhasil ditambahkan";
        } else {
            $errors[] = "✗ Gagal menambahkan kolom: " . mysqli_error($conn);
        }
        
        // Step 2: Tambah foreign key
        $sql2 = "ALTER TABLE rombel ADD CONSTRAINT fk_rombel_wali_kelas 
                 FOREIGN KEY (id_wali_kelas) REFERENCES users(id_user) ON DELETE SET NULL";
        if (mysqli_query($conn, $sql2)) {
            $success[] = "✓ Foreign key constraint berhasil ditambahkan";
        } else {
            $errors[] = "✗ Gagal menambahkan foreign key: " . mysqli_error($conn);
        }
    } else {
        $success[] = "✓ Kolom id_wali_kelas sudah ada, skip step ini";
    }
    
    // Step 3: Migrate data (optional)
    if (isset($_POST['migrate_data'])) {
        $sql3 = "UPDATE rombel r
                 INNER JOIN users u ON r.wali_kelas = u.nama_lengkap AND u.role = 'wali_kelas'
                 SET r.id_wali_kelas = u.id_user
                 WHERE r.wali_kelas IS NOT NULL AND r.wali_kelas != '' AND r.id_wali_kelas IS NULL";
        if (mysqli_query($conn, $sql3)) {
            $affected = mysqli_affected_rows($conn);
            $success[] = "✓ Data berhasil dimigrate ($affected record)";
        } else {
            $errors[] = "✗ Gagal migrate data: " . mysqli_error($conn);
        }
    }
}

// Cek status tabel
$check_fk = mysqli_query($conn, "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                                  WHERE TABLE_SCHEMA = DATABASE() 
                                  AND TABLE_NAME = 'rombel' 
                                  AND CONSTRAINT_NAME = 'fk_rombel_wali_kelas'");
$fk_exists = mysqli_num_rows($check_fk) > 0;

// Hitung data yang bisa dimigrate
$query_migrate = "SELECT COUNT(*) as total FROM rombel r
                  INNER JOIN users u ON r.wali_kelas = u.nama_lengkap AND u.role = 'wali_kelas'
                  WHERE r.wali_kelas IS NOT NULL AND r.wali_kelas != '' AND r.id_wali_kelas IS NULL";
$result_migrate = mysqli_query($conn, $query_migrate);
$can_migrate = mysqli_fetch_assoc($result_migrate)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Database - Relasi Wali Kelas</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .alert-info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .btn { padding: 10px 20px; background-color: #007bff; color: white; border: none; 
               border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn:hover { background-color: #0056b3; }
        .btn-danger { background-color: #dc3545; }
        .btn-danger:hover { background-color: #c82333; }
        .status { padding: 5px 10px; border-radius: 4px; display: inline-block; margin: 5px; }
        .status-ok { background-color: #28a745; color: white; }
        .status-pending { background-color: #ffc107; color: black; }
        h1 { color: #333; }
        h2 { color: #555; margin-top: 30px; }
        ul { line-height: 1.8; }
        .checkbox { margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🔧 Update Database - Relasi Wali Kelas dengan Users</h1>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?php foreach ($success as $msg): ?>
                <div><?php echo $msg; ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $msg): ?>
                <div><?php echo $msg; ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <h2>Status Database</h2>
        <p>
            <strong>Kolom id_wali_kelas:</strong> 
            <span class="status <?php echo $already_updated ? 'status-ok' : 'status-pending'; ?>">
                <?php echo $already_updated ? '✓ Sudah Ada' : '✗ Belum Ada'; ?>
            </span>
        </p>
        <p>
            <strong>Foreign Key Constraint:</strong> 
            <span class="status <?php echo $fk_exists ? 'status-ok' : 'status-pending'; ?>">
                <?php echo $fk_exists ? '✓ Sudah Ada' : '✗ Belum Ada'; ?>
            </span>
        </p>
        <p>
            <strong>Data yang bisa dimigrate:</strong> 
            <span class="status <?php echo $can_migrate > 0 ? 'status-pending' : 'status-ok'; ?>">
                <?php echo $can_migrate; ?> record
            </span>
        </p>
    </div>
    
    <?php if (!$already_updated || !$fk_exists || $can_migrate > 0): ?>
    <div class="card">
        <h2>Jalankan Update</h2>
        <p>Update ini akan:</p>
        <ul>
            <?php if (!$already_updated): ?>
                <li>Menambahkan kolom <code>id_wali_kelas</code> ke tabel rombel</li>
            <?php endif; ?>
            <?php if (!$fk_exists): ?>
                <li>Menambahkan foreign key constraint ke tabel users</li>
            <?php endif; ?>
            <?php if ($can_migrate > 0): ?>
                <li>Memigrate <?php echo $can_migrate; ?> data wali kelas yang cocok</li>
            <?php endif; ?>
        </ul>
        
        <form method="POST" onsubmit="return confirm('Yakin ingin menjalankan update database?');">
            <?php if ($can_migrate > 0): ?>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="migrate_data" value="1" checked>
                    Migrate data existing (mencocokkan nama wali kelas dengan user)
                </label>
            </div>
            <?php endif; ?>
            
            <button type="submit" name="update" class="btn">🚀 Jalankan Update</button>
        </form>
    </div>
    <?php else: ?>
    <div class="alert alert-success">
        <strong>✓ Database sudah terupdate!</strong><br>
        Semua perubahan sudah diterapkan. Anda bisa menghapus file ini sekarang.
    </div>
    <?php endif; ?>
    
    <div class="card">
        <h2>Langkah Selanjutnya</h2>
        <ol>
            <li>Jalankan update database dengan klik tombol di atas</li>
            <li>Pastikan tidak ada error</li>
            <li>Test fitur CRUD rombel di menu admin</li>
            <li>Test dashboard wali kelas</li>
            <li><strong style="color: red;">PENTING: Hapus file ini (update_rombel_wali.php) setelah selesai!</strong></li>
        </ol>
    </div>
    
    <div class="alert alert-warning">
        <strong>⚠️ Peringatan Keamanan:</strong><br>
        File ini memberikan akses untuk mengubah struktur database. Hapus file ini segera setelah proses update selesai untuk keamanan aplikasi.
    </div>
    
</body>
</html>
