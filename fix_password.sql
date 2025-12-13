-- QUICK FIX: Update Password untuk User
-- Jalankan query ini di phpMyAdmin untuk memperbaiki password

-- CARA TERMUDAH: Gunakan MD5 (sementara untuk testing)
-- Login sudah support MD5, bcrypt, dan plain text

UPDATE users SET password = MD5('admin123') WHERE username = 'admin';
UPDATE users SET password = MD5('guru123') WHERE username = 'guru1';
UPDATE users SET password = MD5('guru123') WHERE username = 'guru2';

-- Setelah login berhasil, Anda bisa ganti password melalui menu User Management
-- dan password akan otomatis di-hash dengan bcrypt

-- ALTERNATIF: Gunakan plain text (SANGAT TIDAK AMAN - hanya untuk testing)
-- UPDATE users SET password = 'admin123' WHERE username = 'admin';
-- UPDATE users SET password = 'guru123' WHERE username = 'guru1';
