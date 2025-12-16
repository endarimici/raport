# UPDATE RELASI WALI KELAS - ROMBEL

## Deskripsi Perubahan

Mengubah field `wali_kelas` di tabel `rombel` dari input text biasa menjadi relasi foreign key ke tabel `users` dengan role `wali_kelas`. Ini membuat data wali kelas lebih akurat dan terintegrasi dengan sistem user.

---

## Perubahan yang Dilakukan

### 1. **Database Structure**
- Menambahkan kolom `id_wali_kelas` (INT) di tabel `rombel`
- Menambahkan foreign key constraint ke tabel `users`
- Kolom lama `wali_kelas` (VARCHAR) tetap ada untuk backward compatibility

### 2. **File yang Dimodifikasi**

#### `admin/rombel/add.php`
- Mengambil data users dengan role `wali_kelas`
- Mengubah input text menjadi dropdown select
- Update query INSERT menggunakan `id_wali_kelas`

#### `admin/rombel/edit.php`
- Mengambil data users dengan role `wali_kelas`
- Mengubah input text menjadi dropdown select dengan value selected
- Update query UPDATE menggunakan `id_wali_kelas`

#### `admin/rombel/index.php`
- Update query SELECT dengan JOIN ke tabel `users`
- Menampilkan nama wali kelas dari tabel users
- Tampilkan "-" jika wali kelas belum diset

#### `wali_kelas/dashboard.php`
- Update query untuk filter berdasarkan `id_wali_kelas = $id_user`
- Tidak lagi menggunakan LIKE dengan nama lengkap

### 3. **File Baru yang Dibuat**

- `update_rombel_wali_kelas.sql` - SQL script manual
- `update_rombel_wali.php` - Web-based updater dengan UI

---

## Cara Instalasi

### Opsi 1: Update via Browser (RECOMMENDED) ⭐

1. Buka browser, akses: `http://localhost/raport/update_rombel_wali.php`
2. Lihat status database saat ini
3. Centang opsi "Migrate data existing" jika ada data lama yang ingin dimigrate
4. Klik tombol "🚀 Jalankan Update"
5. Tunggu hingga selesai dan muncul notifikasi success
6. **PENTING: Hapus file `update_rombel_wali.php` setelah selesai!**

### Opsi 2: Update via phpMyAdmin

1. Buka phpMyAdmin
2. Pilih database `raport_smk`
3. Klik tab **SQL**
4. Copy paste query berikut:

```sql
-- Tambah kolom id_wali_kelas
ALTER TABLE rombel 
ADD COLUMN id_wali_kelas INT(11) NULL AFTER wali_kelas;

-- Tambah foreign key
ALTER TABLE rombel
ADD CONSTRAINT fk_rombel_wali_kelas 
FOREIGN KEY (id_wali_kelas) REFERENCES users(id_user) ON DELETE SET NULL;

-- Migrate data existing (optional)
UPDATE rombel r
INNER JOIN users u ON r.wali_kelas = u.nama_lengkap AND u.role = 'wali_kelas'
SET r.id_wali_kelas = u.id_user
WHERE r.wali_kelas IS NOT NULL AND r.wali_kelas != '';
```

5. Klik **Go** / **Kirim**

### Opsi 3: Update via Terminal/CMD

```bash
cd c:\xampp82\htdocs\raport
mysql -u root raport_smk < update_rombel_wali_kelas.sql
```

---

## Testing

### 1. Test CRUD Rombel (Admin)
- Login sebagai admin
- Menu **Rombel** → **Tambah Rombel**
- Field "Wali Kelas" sekarang dropdown
- Pilih wali kelas dari dropdown (data dari users role wali_kelas)
- Simpan dan cek hasilnya

### 2. Test Edit Rombel
- Edit rombel yang sudah ada
- Field wali kelas menampilkan selected value yang benar
- Update dan cek hasilnya

### 3. Test Dashboard Wali Kelas
- Login sebagai user dengan role wali_kelas
- Dashboard hanya menampilkan rombel yang diasuh (berdasarkan id_user)
- Jika tidak muncul rombel, pastikan di admin sudah set wali kelas untuk rombel tersebut

---

## Fitur Baru

### Keuntungan Relasi Database:
✅ **Data Konsisten** - Nama wali kelas selalu sinkron dengan data user  
✅ **Lebih Akurat** - Tidak ada typo atau kesalahan ketik nama  
✅ **Mudah Dikelola** - Update nama user otomatis update di semua rombel  
✅ **Referential Integrity** - Foreign key memastikan data valid  
✅ **Dashboard Otomatis** - Wali kelas otomatis lihat rombel yang diasuh  

---

## Migration Data Lama

Jika ada data rombel lama dengan field `wali_kelas` berisi nama:

1. **Auto Migration**: 
   - Script akan mencoba mencocokkan nama di field `wali_kelas` dengan `nama_lengkap` di tabel users
   - Jika cocok, otomatis di-set ke `id_wali_kelas`

2. **Manual Assignment**:
   - Jika tidak cocok atau tidak ada match, admin perlu edit rombel
   - Pilih wali kelas dari dropdown

---

## Struktur Database

### Tabel: rombel

| Field | Type | Constraint | Keterangan |
|-------|------|------------|------------|
| id_rombel | INT(11) | PK, AI | Primary key |
| nama_rombel | VARCHAR(50) | NOT NULL | Nama rombel |
| id_jurusan | INT(11) | FK | Relasi ke jurusan |
| tingkat | ENUM | NOT NULL | X, XI, XII |
| wali_kelas | VARCHAR(100) | NULL | *Legacy field* |
| **id_wali_kelas** | **INT(11)** | **FK, NULL** | **Relasi ke users** |
| tahun_ajaran | VARCHAR(20) | NULL | Tahun ajaran |

### Foreign Key:
```sql
CONSTRAINT fk_rombel_wali_kelas 
FOREIGN KEY (id_wali_kelas) REFERENCES users(id_user) ON DELETE SET NULL
```

---

## Troubleshooting

### Problem: Dropdown wali kelas kosong
**Solusi**: Pastikan ada user dengan role `wali_kelas` dan status `aktif`

### Problem: Dashboard wali kelas tidak muncul rombel
**Solusi**: 
1. Login sebagai admin
2. Edit rombel yang seharusnya diasuh
3. Pilih wali kelas dari dropdown
4. Simpan

### Problem: Error foreign key constraint
**Solusi**: Pastikan tidak ada `id_wali_kelas` yang merujuk ke `id_user` yang tidak exist

### Problem: Field wali_kelas lama masih ada
**Solusi**: Field lama tidak dihapus untuk backward compatibility. Bisa dihapus manual jika yakin:
```sql
ALTER TABLE rombel DROP COLUMN wali_kelas;
```

---

## Rollback (Jika Diperlukan)

Jika ingin membatalkan perubahan:

```sql
-- Hapus foreign key
ALTER TABLE rombel DROP FOREIGN KEY fk_rombel_wali_kelas;

-- Hapus kolom
ALTER TABLE rombel DROP COLUMN id_wali_kelas;
```

⚠️ **WARNING**: Rollback akan menghilangkan relasi. Pastikan backup data terlebih dahulu!

---

## Checklist

- [ ] Database sudah diupdate (kolom id_wali_kelas ada)
- [ ] Foreign key constraint berhasil ditambahkan
- [ ] Data lama berhasil dimigrate (jika ada)
- [ ] Bisa tambah rombel baru dengan dropdown wali kelas
- [ ] Bisa edit rombel dan dropdown menampilkan selected value
- [ ] Dashboard wali kelas menampilkan rombel yang diasuh
- [ ] File update_rombel_wali.php sudah dihapus

---

**Status:** ✅ SELESAI - Relasi Wali Kelas dengan Users berhasil diterapkan!
