# PANDUAN INSTALASI ROLE WALI KELAS

## Langkah-langkah Instalasi

### 1. Update Database
Jalankan SQL script untuk menambahkan role 'wali_kelas' ke database:

```sql
-- Jalankan query ini di phpMyAdmin atau MySQL client
ALTER TABLE users 
MODIFY COLUMN role ENUM('administrator', 'guru', 'wali_kelas') NOT NULL;
```

Atau bisa menggunakan file SQL yang sudah disediakan:
- Buka phpMyAdmin
- Pilih database raport_smk
- Klik tab SQL
- Copy paste isi file `update_add_wali_kelas.sql`
- Klik Go/Execute

### 2. Membuat User Wali Kelas (Optional)
Untuk membuat sample user wali kelas, jalankan query berikut:

```sql
-- Password: wali123
INSERT INTO users (username, password, nama_lengkap, role, status) VALUES 
('wali1', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Wali Kelas 1, S.Pd', 'wali_kelas', 'aktif');
```

Atau bisa membuat melalui menu Admin > Users > Tambah User, lalu pilih Role = "Wali Kelas"

### 3. File-file yang Sudah Diupdate

#### File yang dimodifikasi:
1. `admin/users/add.php` - Menambahkan opsi role wali kelas di form tambah user
2. `admin/users/edit.php` - Menambahkan opsi role wali kelas di form edit user
3. `index.php` - Menambahkan routing untuk role wali kelas
4. `guru/includes/sidebar.php` - Menghapus menu "Download Raport" dari role guru

#### File baru yang dibuat:
1. `wali_kelas/dashboard.php` - Dashboard untuk wali kelas
2. `wali_kelas/nilai/download.php` - Fitur download raport (dipindah dari guru)
3. `wali_kelas/includes/sidebar.php` - Sidebar untuk wali kelas

### 4. Fitur Role Wali Kelas

Role Wali Kelas memiliki akses ke:
- Dashboard Wali Kelas (menampilkan informasi rombel yang diasuh)
- Download Raport (mengunduh raport siswa dalam format Excel)

### 5. Perbedaan dengan Role Guru

**Role Guru:**
- Input Nilai siswa
- ~~Download Raport~~ (sudah dihapus)

**Role Wali Kelas:**
- Melihat informasi rombel yang diasuh
- Download Raport siswa

### 6. Testing

Untuk menguji instalasi:
1. Login sebagai admin
2. Buat user baru dengan role "Wali Kelas"
3. Logout dan login dengan user wali kelas yang baru dibuat
4. Pastikan dashboard wali kelas muncul
5. Coba fitur download raport

### 7. Catatan Penting

- Pastikan nama wali kelas di tabel `rombel` sesuai dengan nama lengkap user yang memiliki role wali_kelas
- Fitur download raport untuk wali kelas dapat mengunduh raport semua rombel, tidak terbatas hanya rombel yang diasuh
- Data nilai harus sudah diinput oleh guru agar raport dapat diunduh dengan lengkap

---

Instalasi selesai! Role Wali Kelas sudah berhasil ditambahkan ke sistem.
