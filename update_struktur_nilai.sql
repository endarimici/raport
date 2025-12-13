-- UPDATE STRUKTUR TABEL NILAI
-- Jalankan query ini di phpMyAdmin untuk mengubah struktur tabel nilai

-- Backup tabel nilai lama (opsional)
-- CREATE TABLE nilai_backup AS SELECT * FROM nilai;

-- Hapus kolom lama
ALTER TABLE nilai 
DROP COLUMN nilai_pengetahuan,
DROP COLUMN nilai_keterampilan;

-- Tambah kolom baru untuk 6 parameter nilai
ALTER TABLE nilai
ADD COLUMN nilai_formatif_1 DECIMAL(5,2) DEFAULT NULL AFTER id_semester,
ADD COLUMN nilai_formatif_2 DECIMAL(5,2) DEFAULT NULL AFTER nilai_formatif_1,
ADD COLUMN nilai_formatif_3 DECIMAL(5,2) DEFAULT NULL AFTER nilai_formatif_2,
ADD COLUMN nilai_formatif_4 DECIMAL(5,2) DEFAULT NULL AFTER nilai_formatif_3,
ADD COLUMN nilai_sts DECIMAL(5,2) DEFAULT NULL AFTER nilai_formatif_4,
ADD COLUMN nilai_sas DECIMAL(5,2) DEFAULT NULL AFTER nilai_sts;

-- Kolom nilai_akhir dan predikat tetap ada untuk menyimpan hasil perhitungan
