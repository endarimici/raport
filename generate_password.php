<?php
// Script untuk generate password hash yang benar

echo "Password Hash Generator\n";
echo "======================\n\n";

$admin_password = 'admin123';
$guru_password = 'guru123';

$admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);
$guru_hash = password_hash($guru_password, PASSWORD_DEFAULT);

echo "Password untuk admin123:\n";
echo $admin_hash . "\n\n";

echo "Password untuk guru123:\n";
echo $guru_hash . "\n\n";

echo "\n======================\n";
echo "SQL Update Query:\n\n";

echo "UPDATE users SET password = '$admin_hash' WHERE username = 'admin';\n";
echo "UPDATE users SET password = '$guru_hash' WHERE username = 'guru1';\n";
echo "UPDATE users SET password = '$guru_hash' WHERE username = 'guru2';\n";
?>
