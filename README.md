# Aplikasi Raport SMK

Aplikasi Raport SMK adalah sistem informasi berbasis web untuk mengelola data raport siswa SMK menggunakan PHP Native dan MySQL.

## Fitur Aplikasi

### Administrator
1. **Login & Manajemen User**
   - Login dengan role Administrator dan Guru
   - Tambah, edit, dan hapus user
   
2. **Master Data**
   - Manajemen Jurusan
   - Manajemen Rombongan Belajar (Rombel)
   - Manajemen Mata Pelajaran
   - Manajemen Semester & Tahun Akademik
   - Manajemen Data Siswa

3. **Nilai & Raport**
   - Lihat semua data nilai siswa
   - Filter nilai berdasarkan rombel, mapel, dan semester

### Guru
1. **Input Nilai**
   - Input nilai pengetahuan dan keterampilan siswa
   - Sistem otomatis menghitung nilai akhir dan predikat
   
2. **Download Raport**
   - Download raport dalam format Excel (.xls)
   - Raport berisi nilai lengkap semua mata pelajaran

## Instalasi

### Persyaratan
- XAMPP (PHP 7.4+ dan MySQL)
- Web Browser

### Langkah Instalasi

1. **Copy Folder ke XAMPP**
   ```
   Copy folder "raport" ke: C:\xampp82\htdocs\
   ```

2. **Buat Database**
   - Buka phpMyAdmin: http://localhost/phpmyadmin
   - Buat database baru dengan nama: `raport_smk`
   - Import file `database.sql` yang ada di folder raport

3. **Konfigurasi Database** (Optional)
   File `config.php` sudah dikonfigurasi dengan:
   - Host: localhost
   - Username: root
   - Password: (kosong)
   - Database: raport_smk

4. **Akses Aplikasi**
   Buka browser dan akses: http://localhost/raport

## Login Default

### Administrator
- Username: `admin`
- Password: `admin123`

### Guru
- Username: `guru1`
- Password: `guru123`

## Struktur Database

Database terdiri dari 10 tabel utama:
1. `users` - Data pengguna (admin & guru)
2. `jurusan` - Data jurusan
3. `rombel` - Data rombongan belajar
4. `mata_pelajaran` - Data mata pelajaran
5. `semester` - Data semester & tahun akademik
6. `siswa` - Data siswa
7. `mapel_guru` - Relasi guru dengan mata pelajaran yang diajar
8. `nilai` - Data nilai siswa

## Cara Penggunaan

### Untuk Administrator

1. **Login** menggunakan akun administrator
2. **Tambah Data Master**:
   - Tambah Jurusan terlebih dahulu
   - Tambah Mata Pelajaran
   - Tambah Rombel (pilih jurusan yang sudah dibuat)
   - Tambah Semester (pastikan ada semester yang aktif)
   - Tambah Data Siswa (pilih rombel)
3. **Tambah User Guru** untuk dapat input nilai
4. **Monitoring Nilai** melalui menu Nilai Siswa

### Untuk Guru

1. **Login** menggunakan akun guru
2. **Input Nilai**:
   - Pilih Rombel dan Mata Pelajaran yang Anda ajar
   - Klik Tampilkan untuk melihat daftar siswa
   - Input nilai Pengetahuan dan Keterampilan
   - Tambahkan deskripsi jika diperlukan
   - Klik Simpan Semua Nilai
3. **Download Raport**:
   - Pilih Semester dan Rombel
   - Klik Download Raport (Excel)
   - File akan terdownload otomatis

## Sistem Penilaian

### Perhitungan Nilai
- **Nilai Akhir** = (Nilai Pengetahuan + Nilai Keterampilan) / 2

### Predikat
- A = Nilai 90 - 100
- B = Nilai 80 - 89
- C = Nilai 70 - 79
- D = Nilai < 70

## Teknologi yang Digunakan

- **Backend**: PHP Native
- **Database**: MySQL
- **Frontend**: HTML, CSS (Custom)
- **Server**: Apache (XAMPP)

## Struktur Folder

```
raport/
├── admin/              # Panel Administrator
│   ├── dashboard.php
│   ├── users/         # Manajemen User
│   ├── jurusan/       # Manajemen Jurusan
│   ├── rombel/        # Manajemen Rombel
│   ├── mapel/         # Manajemen Mata Pelajaran
│   ├── siswa/         # Manajemen Siswa
│   ├── semester/      # Manajemen Semester
│   ├── nilai/         # Lihat Nilai
│   └── includes/      # File include (sidebar)
├── guru/              # Panel Guru
│   ├── dashboard.php
│   ├── nilai/         # Input Nilai & Download
│   └── includes/      # File include (sidebar)
├── assets/
│   └── css/          # File CSS
├── config.php        # Konfigurasi Database
├── index.php         # Halaman Login
├── logout.php        # Logout
├── database.sql      # File SQL Database
└── README.md         # Dokumentasi
```

## Troubleshooting

### Database Connection Error
- Pastikan MySQL di XAMPP sudah running
- Cek konfigurasi di `config.php`
- Pastikan database `raport_smk` sudah dibuat

### Login Gagal
- Pastikan username dan password benar
- Cek status user di database (harus 'aktif')

### Nilai Tidak Tersimpan
- Pastikan ada semester yang aktif
- Pastikan guru sudah di-assign ke mata pelajaran dan rombel

## Keamanan

- Password di-hash menggunakan `password_hash()` PHP
- Input di-sanitize menggunakan `mysqli_real_escape_string()`
- Session-based authentication
- Role-based access control

## Pengembangan Lebih Lanjut

Fitur yang bisa ditambahkan:
1. Cetak raport dalam format PDF
2. Dashboard statistik lebih lengkap
3. Export/Import data siswa dari Excel
4. Sistem notifikasi
5. Riwayat perubahan nilai
6. Absensi siswa
7. Nilai ekstrakurikuler

## Lisensi

Aplikasi ini dibuat untuk keperluan pembelajaran dan dapat digunakan secara bebas.

## Kontak & Support

Untuk pertanyaan atau bantuan, silakan hubungi administrator sistem.

---

**Dibuat dengan ❤️ menggunakan PHP Native**
