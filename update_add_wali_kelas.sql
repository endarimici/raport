-- Update database untuk menambahkan role wali kelas
-- Jalankan script ini untuk menambahkan role wali kelas ke aplikasi raport

-- 1. Ubah ENUM role di table users untuk menambahkan 'wali_kelas'
ALTER TABLE users 
MODIFY COLUMN role ENUM('administrator', 'guru', 'wali_kelas') NOT NULL;

-- 2. Optional: Update contoh data wali kelas (password: wali123)
-- Uncomment jika ingin menambahkan sample user wali kelas
-- INSERT INTO users (username, password, nama_lengkap, role, status) VALUES 
-- ('wali1', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Wali Kelas 1, S.Pd', 'wali_kelas', 'aktif');

-- Selesai!
