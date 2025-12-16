# RINGKASAN PERUBAHAN - PENAMBAHAN ROLE WALI KELAS

## Tanggal: 16 Desember 2025

### Deskripsi Perubahan
Menambahkan role baru "wali_kelas" ke dalam sistem raport SMK. Role ini memiliki akses khusus untuk mengunduh raport siswa. Fitur download raport yang sebelumnya ada di role "guru" telah dipindahkan ke role "wali_kelas".

---

## File yang Dimodifikasi

### 1. `admin/users/add.php`
**Perubahan:** Menambahkan opsi "Wali Kelas" di dropdown role saat menambah user baru
```php
<option value="wali_kelas">Wali Kelas</option>
```

### 2. `admin/users/edit.php`
**Perubahan:** Menambahkan opsi "Wali Kelas" di dropdown role saat mengedit user
```php
<option value="wali_kelas" <?php echo $user['role'] == 'wali_kelas' ? 'selected' : ''; ?>>Wali Kelas</option>
```

### 3. `index.php`
**Perubahan:** Menambahkan routing untuk role wali_kelas
- Redirect saat sudah login: menambah kondisi untuk role wali_kelas ke `wali_kelas/dashboard.php`
- Redirect setelah login berhasil: menambah kondisi untuk role wali_kelas ke `wali_kelas/dashboard.php`

### 4. `guru/includes/sidebar.php`
**Perubahan:** Menghapus menu "Download Raport" dari sidebar guru
- Menu yang dihapus: 💾 Download Raport

---

## File Baru yang Dibuat

### 1. Struktur Folder
```
wali_kelas/
├── dashboard.php
├── includes/
│   └── sidebar.php
└── nilai/
    └── download.php
```

### 2. `wali_kelas/dashboard.php`
**Fungsi:** Dashboard untuk role wali kelas
- Menampilkan jumlah rombel yang diasuh
- Menampilkan total siswa dari rombel yang diasuh
- Menampilkan semester aktif
- Menampilkan daftar rombel yang diasuh dalam tabel

### 3. `wali_kelas/includes/sidebar.php`
**Fungsi:** Sidebar navigasi untuk wali kelas
Menu yang tersedia:
- 📊 Dashboard
- 💾 Download Raport

### 4. `wali_kelas/nilai/download.php`
**Fungsi:** Fitur download raport siswa dalam format Excel
- Memilih semester dan rombel
- Mengunduh raport dengan format Excel (.xls)
- Menampilkan nilai semua mata pelajaran
- Menampilkan status ketuntasan (Tuntas/Belum Tuntas)
- Menampilkan rata-rata nilai

### 5. `update_add_wali_kelas.sql`
**Fungsi:** SQL script untuk update database
```sql
ALTER TABLE users 
MODIFY COLUMN role ENUM('administrator', 'guru', 'wali_kelas') NOT NULL;
```

### 6. `INSTALASI_WALI_KELAS.md`
**Fungsi:** Panduan instalasi lengkap untuk penambahan role wali kelas

---

## Perubahan Database

### Table: `users`
**Field yang diubah:** `role`
- **Sebelum:** ENUM('administrator', 'guru')
- **Sesudah:** ENUM('administrator', 'guru', 'wali_kelas')

---

## Hak Akses Per Role

### Role Administrator
- Semua menu admin (users, jurusan, rombel, mapel, guru_mapel, semester, siswa, nilai)

### Role Guru
- Dashboard Guru
- Input Nilai
- ~~Download Raport~~ (sudah dihapus)

### Role Wali Kelas (BARU)
- Dashboard Wali Kelas
- Download Raport

---

## Cara Menggunakan

### 1. Update Database
Jalankan file `update_add_wali_kelas.sql` di phpMyAdmin

### 2. Membuat User Wali Kelas
- Login sebagai admin
- Menu Users > Tambah User
- Isi form dan pilih Role = "Wali Kelas"
- Simpan

### 3. Login sebagai Wali Kelas
- Logout dari admin
- Login dengan username dan password wali kelas yang baru dibuat
- Akan diarahkan ke Dashboard Wali Kelas

### 4. Download Raport
- Klik menu "Download Raport"
- Pilih Semester
- Pilih Rombel
- Klik tombol "Download Raport (Excel)"
- File Excel akan terdownload

---

## Testing Checklist

- [ ] Database sudah diupdate (ALTER TABLE users)
- [ ] Bisa membuat user baru dengan role Wali Kelas di admin
- [ ] Bisa login sebagai Wali Kelas
- [ ] Dashboard Wali Kelas tampil dengan benar
- [ ] Menu Download Raport berfungsi
- [ ] File Excel raport terdownload dengan benar
- [ ] Menu Download Raport sudah tidak ada di sidebar Guru
- [ ] Guru masih bisa login dan akses Dashboard serta Input Nilai

---

## Catatan Tambahan

1. **Relasi Wali Kelas dengan Rombel:**
   - Dashboard wali kelas menampilkan rombel berdasarkan field `wali_kelas` di tabel `rombel`
   - Pastikan nama lengkap di user wali kelas sesuai dengan nama di field `wali_kelas` di tabel rombel

2. **Fitur Download Raport:**
   - Wali kelas dapat mengunduh raport dari semua rombel (tidak terbatas hanya rombel yang diasuh)
   - Ini memudahkan jika ada substitusi atau kebutuhan lainnya

3. **Backward Compatibility:**
   - Perubahan ini tidak mempengaruhi role administrator dan guru yang sudah ada
   - Semua fitur yang sudah ada tetap berfungsi normal

---

## Support

Jika ada masalah atau pertanyaan, silakan hubungi developer atau baca panduan lengkap di `INSTALASI_WALI_KELAS.md`.

---

**Status:** ✅ SELESAI - Semua perubahan telah berhasil diterapkan
