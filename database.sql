-- Database: raport_smk
-- Buat database terlebih dahulu

CREATE DATABASE IF NOT EXISTS raport_smk;
USE raport_smk;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id_user INT(11) PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('administrator', 'guru') NOT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: jurusan
CREATE TABLE IF NOT EXISTS jurusan (
    id_jurusan INT(11) PRIMARY KEY AUTO_INCREMENT,
    kode_jurusan VARCHAR(20) NOT NULL UNIQUE,
    nama_jurusan VARCHAR(100) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: rombel (rombongan belajar)
CREATE TABLE IF NOT EXISTS rombel (
    id_rombel INT(11) PRIMARY KEY AUTO_INCREMENT,
    nama_rombel VARCHAR(50) NOT NULL,
    id_jurusan INT(11) NOT NULL,
    tingkat ENUM('X', 'XI', 'XII') NOT NULL,
    wali_kelas VARCHAR(100),
    tahun_ajaran VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_jurusan) REFERENCES jurusan(id_jurusan) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: mata_pelajaran
CREATE TABLE IF NOT EXISTS mata_pelajaran (
    id_mapel INT(11) PRIMARY KEY AUTO_INCREMENT,
    kode_mapel VARCHAR(20) NOT NULL UNIQUE,
    nama_mapel VARCHAR(100) NOT NULL,
    kelompok ENUM('A', 'B', 'C') NOT NULL COMMENT 'A=Umum, B=Kejuruan, C=Muatan Lokal',
    kkm DECIMAL(5,2) DEFAULT 75.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: semester
CREATE TABLE IF NOT EXISTS semester (
    id_semester INT(11) PRIMARY KEY AUTO_INCREMENT,
    nama_semester VARCHAR(50) NOT NULL,
    semester ENUM('Ganjil', 'Genap') NOT NULL,
    tahun_ajaran VARCHAR(20) NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'nonaktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: siswa
CREATE TABLE IF NOT EXISTS siswa (
    id_siswa INT(11) PRIMARY KEY AUTO_INCREMENT,
    nis VARCHAR(20) NOT NULL UNIQUE,
    nisn VARCHAR(20),
    nama_lengkap VARCHAR(100) NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    tempat_lahir VARCHAR(50),
    tanggal_lahir DATE,
    alamat TEXT,
    telepon VARCHAR(20),
    id_rombel INT(11),
    status ENUM('aktif', 'lulus', 'keluar') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rombel) REFERENCES rombel(id_rombel) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: mapel_guru (relasi mata pelajaran dengan guru)
CREATE TABLE IF NOT EXISTS mapel_guru (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    id_user INT(11) NOT NULL,
    id_mapel INT(11) NOT NULL,
    id_rombel INT(11) NOT NULL,
    id_semester INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_mapel) REFERENCES mata_pelajaran(id_mapel) ON DELETE CASCADE,
    FOREIGN KEY (id_rombel) REFERENCES rombel(id_rombel) ON DELETE CASCADE,
    FOREIGN KEY (id_semester) REFERENCES semester(id_semester) ON DELETE CASCADE,
    UNIQUE KEY unique_mapel_guru (id_user, id_mapel, id_rombel, id_semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: nilai
CREATE TABLE IF NOT EXISTS nilai (
    id_nilai INT(11) PRIMARY KEY AUTO_INCREMENT,
    id_siswa INT(11) NOT NULL,
    id_mapel INT(11) NOT NULL,
    id_semester INT(11) NOT NULL,
    nilai_formatif_1 DECIMAL(5,2) DEFAULT NULL,
    nilai_formatif_2 DECIMAL(5,2) DEFAULT NULL,
    nilai_formatif_3 DECIMAL(5,2) DEFAULT NULL,
    nilai_formatif_4 DECIMAL(5,2) DEFAULT NULL,
    nilai_sts DECIMAL(5,2) DEFAULT NULL,
    nilai_sas DECIMAL(5,2) DEFAULT NULL,
    nilai_akhir DECIMAL(5,2),
    predikat ENUM('A', 'B', 'C', 'D') DEFAULT NULL,
    deskripsi TEXT,
    id_guru INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa) ON DELETE CASCADE,
    FOREIGN KEY (id_mapel) REFERENCES mata_pelajaran(id_mapel) ON DELETE CASCADE,
    FOREIGN KEY (id_semester) REFERENCES semester(id_semester) ON DELETE CASCADE,
    FOREIGN KEY (id_guru) REFERENCES users(id_user) ON DELETE CASCADE,
    UNIQUE KEY unique_nilai (id_siswa, id_mapel, id_semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, nama_lengkap, role, status) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'administrator', 'aktif');

-- Insert sample data untuk guru (password: guru123)
INSERT INTO users (username, password, nama_lengkap, role, status) VALUES 
('guru1', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Budi Santoso, S.Pd', 'guru', 'aktif'),
('guru2', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Siti Rahayu, S.Kom', 'guru', 'aktif');

-- Insert sample data jurusan
INSERT INTO jurusan (kode_jurusan, nama_jurusan, keterangan) VALUES 
('TKJ', 'Teknik Komputer dan Jaringan', 'Kompetensi keahlian yang mempelajari tentang cara instalasi PC, instalasi LAN, dan instalasi Internet'),
('RPL', 'Rekayasa Perangkat Lunak', 'Kompetensi keahlian yang mempelajari tentang pemrograman komputer'),
('MM', 'Multimedia', 'Kompetensi keahlian yang mempelajari tentang desain grafis, animasi, dan video editing'),
('AKL', 'Akuntansi dan Keuangan Lembaga', 'Kompetensi keahlian yang mempelajari tentang akuntansi dan keuangan');

-- Insert sample data mata pelajaran
INSERT INTO mata_pelajaran (kode_mapel, nama_mapel, kelompok, kkm) VALUES 
('MTK', 'Matematika', 'A', 75.00),
('BING', 'Bahasa Inggris', 'A', 75.00),
('BIND', 'Bahasa Indonesia', 'A', 75.00),
('PKN', 'Pendidikan Kewarganegaraan', 'A', 75.00),
('PJOK', 'Pendidikan Jasmani dan Olahraga', 'A', 75.00),
('SIMDIG', 'Simulasi dan Komunikasi Digital', 'B', 75.00),
('JARKOM', 'Jaringan Komputer', 'B', 75.00),
('PEMWEB', 'Pemrograman Web', 'B', 75.00),
('BASDAT', 'Basis Data', 'B', 75.00);

-- Insert sample semester
INSERT INTO semester (nama_semester, semester, tahun_ajaran, tanggal_mulai, tanggal_selesai, status) VALUES 
('Semester Ganjil 2024/2025', 'Ganjil', '2024/2025', '2024-07-15', '2024-12-20', 'aktif'),
('Semester Genap 2024/2025', 'Genap', '2024/2025', '2025-01-07', '2025-06-20', 'nonaktif');

-- Insert sample rombel
INSERT INTO rombel (nama_rombel, id_jurusan, tingkat, wali_kelas, tahun_ajaran) VALUES 
('X TKJ 1', 1, 'X', 'Budi Santoso, S.Pd', '2024/2025'),
('X TKJ 2', 1, 'X', 'Siti Rahayu, S.Kom', '2024/2025'),
('X RPL 1', 2, 'X', 'Ahmad Fauzi, S.Kom', '2024/2025'),
('XI TKJ 1', 1, 'XI', 'Dewi Lestari, S.Pd', '2024/2025'),
('XI RPL 1', 2, 'XI', 'Eko Prasetyo, S.Kom', '2024/2025');

-- Insert sample siswa
INSERT INTO siswa (nis, nisn, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, telepon, id_rombel, status) VALUES 
('2024001', '0051234567', 'Ahmad Rizki', 'L', 'Jakarta', '2008-05-15', 'Jl. Merdeka No. 10', '081234567890', 1, 'aktif'),
('2024002', '0051234568', 'Siti Nurhaliza', 'P', 'Bandung', '2008-08-20', 'Jl. Sudirman No. 25', '081234567891', 1, 'aktif'),
('2024003', '0051234569', 'Budi Santoso', 'L', 'Surabaya', '2008-03-10', 'Jl. Gatot Subroto No. 5', '081234567892', 1, 'aktif'),
('2024004', '0051234570', 'Dewi Lestari', 'P', 'Yogyakarta', '2008-11-25', 'Jl. Ahmad Yani No. 15', '081234567893', 2, 'aktif'),
('2024005', '0051234571', 'Eko Prasetyo', 'L', 'Semarang', '2008-07-30', 'Jl. Diponegoro No. 20', '081234567894', 2, 'aktif');

-- Insert sample mapel_guru (assign guru ke mata pelajaran dan rombel untuk semester aktif)
-- Guru 1 mengajar Matematika dan Bahasa Inggris di X TKJ 1 dan X TKJ 2
INSERT INTO mapel_guru (id_user, id_mapel, id_rombel, id_semester) VALUES 
(2, 1, 1, 1), -- Guru1 - Matematika - X TKJ 1 - Semester Ganjil 2024/2025
(2, 2, 1, 1), -- Guru1 - Bahasa Inggris - X TKJ 1 - Semester Ganjil 2024/2025
(2, 1, 2, 1), -- Guru1 - Matematika - X TKJ 2 - Semester Ganjil 2024/2025
-- Guru 2 mengajar Pemrograman Web dan Basis Data di X TKJ 1
(3, 8, 1, 1), -- Guru2 - Pemrograman Web - X TKJ 1 - Semester Ganjil 2024/2025
(3, 9, 1, 1); -- Guru2 - Basis Data - X TKJ 1 - Semester Ganjil 2024/2025
