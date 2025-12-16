-- Update struktur database untuk relasi wali kelas dengan users
-- Jalankan script ini untuk menambahkan relasi wali kelas ke tabel users

-- 1. Tambah kolom id_wali_kelas ke tabel rombel
ALTER TABLE rombel 
ADD COLUMN id_wali_kelas INT(11) NULL AFTER wali_kelas;

-- 2. Tambah foreign key constraint
ALTER TABLE rombel
ADD CONSTRAINT fk_rombel_wali_kelas 
FOREIGN KEY (id_wali_kelas) REFERENCES users(id_user) ON DELETE SET NULL;

-- 3. Update data existing (opsional - hanya jika ingin migrate data lama)
-- Uncomment jika ingin mencoba match nama wali_kelas dengan users
-- UPDATE rombel r
-- INNER JOIN users u ON r.wali_kelas = u.nama_lengkap AND u.role = 'wali_kelas'
-- SET r.id_wali_kelas = u.id_user
-- WHERE r.wali_kelas IS NOT NULL AND r.wali_kelas != '';

-- 4. Opsional: Bisa hapus kolom wali_kelas lama setelah yakin migrasi berhasil
-- ALTER TABLE rombel DROP COLUMN wali_kelas;

-- Selesai!
