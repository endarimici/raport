-- Update struktur tabel mata_pelajaran
-- Menambahkan field deskripsi untuk setiap predikat nilai

ALTER TABLE mata_pelajaran 
ADD COLUMN deskripsi_a TEXT NULL AFTER kkm,
ADD COLUMN deskripsi_b TEXT NULL AFTER deskripsi_a,
ADD COLUMN deskripsi_c TEXT NULL AFTER deskripsi_b,
ADD COLUMN deskripsi_d TEXT NULL AFTER deskripsi_c;

-- Contoh update data (optional)
-- UPDATE mata_pelajaran SET 
-- deskripsi_a = 'Siswa menunjukkan pemahaman yang sangat baik dan mampu menerapkan konsep dengan sangat efektif.',
-- deskripsi_b = 'Siswa menunjukkan pemahaman yang baik dan mampu menerapkan konsep dengan cukup efektif.',
-- deskripsi_c = 'Siswa menunjukkan pemahaman yang cukup dan perlu lebih banyak latihan dalam menerapkan konsep.',
-- deskripsi_d = 'Siswa perlu bimbingan lebih lanjut untuk memahami dan menerapkan konsep dasar.'
-- WHERE id_mapel = 1;
