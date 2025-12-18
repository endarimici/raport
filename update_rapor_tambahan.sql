-- Tabel untuk data rapor tambahan (kokurikuler, kehadiran, catatan)
CREATE TABLE IF NOT EXISTS rapor_tambahan (
    id_rapor_tambahan INT PRIMARY KEY AUTO_INCREMENT,
    id_siswa INT NOT NULL,
    id_semester INT NOT NULL,
    deskripsi_kokurikuler TEXT,
    sakit INT DEFAULT 0,
    izin INT DEFAULT 0,
    tanpa_keterangan INT DEFAULT 0,
    catatan_wali_kelas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa) ON DELETE CASCADE,
    FOREIGN KEY (id_semester) REFERENCES semester(id_semester) ON DELETE CASCADE,
    UNIQUE KEY unique_siswa_semester (id_siswa, id_semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk ekstrakurikuler siswa
CREATE TABLE IF NOT EXISTS rapor_ekstrakurikuler (
    id_ekstrakurikuler INT PRIMARY KEY AUTO_INCREMENT,
    id_siswa INT NOT NULL,
    id_semester INT NOT NULL,
    nama_ekstrakurikuler VARCHAR(100) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa) ON DELETE CASCADE,
    FOREIGN KEY (id_semester) REFERENCES semester(id_semester) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
