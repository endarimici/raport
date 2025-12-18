# Panduan Instalasi dan Penggunaan Fitur Review Rapor

## Instalasi Database

Jalankan file SQL berikut untuk membuat tabel yang diperlukan:

```bash
mysql -u root -p raport_smk < update_rapor_tambahan.sql
```

Atau jalankan manual melalui phpMyAdmin dengan membuka file `update_rapor_tambahan.sql`

## Tabel yang Dibuat

### 1. Tabel `rapor_tambahan`
Menyimpan data kokurikuler, ketidakhadiran, dan catatan wali kelas per siswa per semester.

Struktur:
- `id_rapor_tambahan` (Primary Key)
- `id_siswa` (Foreign Key ke tabel siswa)
- `id_semester` (Foreign Key ke tabel semester)
- `deskripsi_kokurikuler` (TEXT) - Deskripsi kegiatan kokurikuler siswa
- `sakit` (INT) - Jumlah hari tidak hadir karena sakit
- `izin` (INT) - Jumlah hari tidak hadir karena izin
- `tanpa_keterangan` (INT) - Jumlah hari tidak hadir tanpa keterangan
- `catatan_wali_kelas` (TEXT) - Catatan dari wali kelas untuk siswa
- `created_at` & `updated_at` (TIMESTAMP)

### 2. Tabel `rapor_ekstrakurikuler`
Menyimpan data ekstrakurikuler yang diikuti siswa per semester.

Struktur:
- `id_ekstrakurikuler` (Primary Key)
- `id_siswa` (Foreign Key ke tabel siswa)
- `id_semester` (Foreign Key ke tabel semester)
- `nama_ekstrakurikuler` (VARCHAR) - Nama kegiatan ekstrakurikuler
- `keterangan` (TEXT) - Keterangan/penilaian ekstrakurikuler
- `created_at` & `updated_at` (TIMESTAMP)

## Cara Menggunakan

### Untuk Administrator:

1. **Akses Menu Nilai Rapor**
   - Login sebagai Administrator
   - Klik menu "📄 Nilai Rapor" di sidebar

2. **Pilih Rombel dan Semester**
   - Pilih rombel dari dropdown
   - Pilih semester yang diinginkan
   - Klik tombol "Filter"

3. **Review Rapor Siswa**
   - Klik tombol "✏️ Review Rapor" pada siswa yang ingin di-review
   - Isi form yang tersedia:
     - **Deskripsi Kokurikuler**: Deskripsi kegiatan kokurikuler siswa
     - **Ekstrakurikuler**: Nama kegiatan dan keterangan (bisa multiple, klik "+ Tambah Ekstrakurikuler")
     - **Ketidakhadiran**: Jumlah hari sakit, izin, dan tanpa keterangan
     - **Catatan Wali Kelas**: Catatan untuk siswa
   - Klik "💾 Simpan Data Rapor"

4. **Preview Rapor**
   - Klik tombol "📄 Preview Rapor" untuk melihat rapor dalam format PDF
   - Data yang sudah diisi akan otomatis muncul di rapor
   - Klik "🖨️ Cetak / Download PDF" untuk mencetak atau menyimpan sebagai PDF

### Untuk Wali Kelas:

1. **Akses Menu Nilai Rapor**
   - Login sebagai Wali Kelas
   - Klik menu "📄 Nilai Rapor" di sidebar

2. **Lihat Daftar Siswa**
   - Sistem otomatis menampilkan siswa dari rombel yang Anda handle
   - Pilih semester jika diperlukan

3. **Review dan Preview Rapor**
   - Ikuti langkah yang sama seperti Administrator (poin 3 dan 4)
   - Wali kelas hanya bisa mengakses siswa dari rombel yang ditugaskan

## Fitur Review Rapor

### Form Input:

1. **Deskripsi Kokurikuler**
   - Textarea untuk menuliskan deskripsi kegiatan kokurikuler siswa
   - Contoh: "Ananda sudah baik dalam kreativitas yang terlihat dari kemampuan menemukan dan mengembangkan alternatif solusi yang efektif..."

2. **Ekstrakurikuler**
   - Dapat menambahkan multiple ekstrakurikuler
   - Setiap baris berisi: Nama Ekstrakurikuler + Keterangan
   - Gunakan tombol "✖" untuk menghapus baris
   - Gunakan tombol "+ Tambah Ekstrakurikuler" untuk menambah baris baru

3. **Ketidakhadiran**
   - Input number untuk Sakit, Izin, dan Tanpa Keterangan
   - Dalam satuan hari

4. **Catatan Wali Kelas**
   - Textarea untuk menuliskan catatan khusus untuk siswa

### Preview PDF:

- Menampilkan format rapor sesuai standar
- Data yang sudah diisi akan otomatis muncul:
  - Nilai mata pelajaran dari database nilai
  - Deskripsi kokurikuler
  - Data ekstrakurikuler dalam bentuk tabel
  - Jumlah ketidakhadiran
  - Catatan wali kelas
- Jika data belum diisi, akan menampilkan pesan placeholder
- Dapat dicetak langsung atau disimpan sebagai PDF melalui browser

## Catatan Penting

1. **Semester Wajib Dipilih**: Review rapor hanya bisa dilakukan jika semester sudah dipilih
2. **Auto-save**: Data yang sudah disimpan dapat di-edit kapan saja
3. **Multiple Ekstrakurikuler**: Bisa menambahkan lebih dari satu kegiatan ekstrakurikuler
4. **Akses Control**: Wali kelas hanya bisa mengakses siswa dari rombel mereka sendiri
5. **Real-time Preview**: Setelah menyimpan data, bisa langsung klik "Preview Rapor" untuk melihat hasilnya

## Files yang Dibuat/Dimodifikasi

1. **Database:**
   - `update_rapor_tambahan.sql` - Script SQL untuk membuat tabel

2. **Admin:**
   - `admin/rapor/index.php` - Halaman daftar siswa untuk admin
   - `admin/rapor/review.php` - Form review rapor untuk admin
   - `admin/rapor/preview.php` - Preview PDF rapor untuk admin
   - `admin/includes/sidebar.php` - Menambah menu Nilai Rapor

3. **Wali Kelas:**
   - `wali_kelas/rapor/index.php` - Halaman daftar siswa untuk wali kelas
   - `wali_kelas/rapor/review.php` - Form review rapor untuk wali kelas
   - `wali_kelas/rapor/preview.php` - Preview PDF rapor untuk wali kelas
   - `wali_kelas/includes/sidebar.php` - Menambah menu Nilai Rapor

## Troubleshooting

### Tabel tidak ditemukan
- Pastikan sudah menjalankan file SQL `update_rapor_tambahan.sql`
- Cek di phpMyAdmin apakah tabel `rapor_tambahan` dan `rapor_ekstrakurikuler` sudah ada

### Data tidak muncul di preview
- Pastikan semester sudah dipilih saat review dan preview
- Pastikan data sudah disimpan dengan menekan tombol "Simpan Data Rapor"

### Wali kelas tidak bisa akses siswa tertentu
- Pastikan wali kelas sudah ditugaskan ke rombel di menu Rombongan Belajar
- Cek field `id_wali_kelas` di tabel `rombel`

## Support

Jika ada pertanyaan atau masalah, silakan hubungi administrator sistem.
