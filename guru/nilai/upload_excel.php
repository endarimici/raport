<?php
require_once '../../config.php';
requireRole('guru');

$id_user = $_SESSION['user_id'];
$error = '';
$success = '';
$preview_data = [];

// Ambil data semester aktif
$query_semester = "SELECT * FROM semester WHERE status = 'aktif' LIMIT 1";
$result_semester = mysqli_query($conn, $query_semester);
$semester_aktif = mysqli_fetch_assoc($result_semester);

// Proses upload file
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_excel'])) {
    $file = $_FILES['file_excel'];
    
    // Validasi file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Error upload file!';
    } else {
        $allowed_ext = ['xls', 'xlsx'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_ext)) {
            $error = 'Format file harus .xls atau .xlsx!';
        } else {
            // Baca file
            $file_content = file_get_contents($file['tmp_name']);
            
            // Parse HTML table (karena file Excel kita sebenarnya HTML)
            $dom = new DOMDocument();
            @$dom->loadHTML($file_content);
            $tables = $dom->getElementsByTagName('table');
            
            if ($tables->length > 0) {
                $table = $tables->item(0);
                $rows = $table->getElementsByTagName('tr');
                
                // Cari metadata (ID_ROMBEL, ID_MAPEL, ID_GURU, ID_SEMESTER)
                $id_rombel = '';
                $id_mapel = '';
                $id_guru_file = '';
                $id_semester_file = '';
                
                for ($i = 0; $i < min(10, $rows->length); $i++) {
                    $row = $rows->item($i);
                    $cells = $row->getElementsByTagName('td');
                    if ($cells->length >= 8) {
                        $label = trim($cells->item(0)->nodeValue);
                        if ($label == 'ID_ROMBEL') {
                            $id_rombel = trim($cells->item(1)->nodeValue);
                            $id_mapel = trim($cells->item(3)->nodeValue);
                            $id_guru_file = trim($cells->item(5)->nodeValue);
                            $id_semester_file = trim($cells->item(7)->nodeValue);
                            break;
                        }
                    }
                }
                
                // Validasi metadata ditemukan
                if (empty($id_rombel) || empty($id_mapel) || empty($id_guru_file)) {
                    $error = 'Format file tidak valid! Gunakan file Excel dari menu Download Excel.';
                } elseif ($id_guru_file != $id_user) {
                    $error = "File ini bukan milik Anda! File ini untuk guru dengan ID: $id_guru_file";
                } elseif ($id_semester_file != $semester_aktif['id_semester']) {
                    $error = 'File ini untuk semester yang berbeda!';
                } else {
                    // Verifikasi guru mengajar di rombel dan mapel ini
                    $check_guru = "SELECT * FROM mapel_guru 
                                  WHERE id_user = $id_user 
                                  AND id_rombel = $id_rombel 
                                  AND id_mapel = $id_mapel";
                    $result_check = mysqli_query($conn, $check_guru);
                    
                    if (mysqli_num_rows($result_check) == 0) {
                        $error = 'Anda tidak mengajar di rombel dan mata pelajaran ini!';
                    } else {
                            // Parse data siswa - cari header row
                            $header_found = false;
                            $data_start = 0;
                            
                            for ($i = 0; $i < $rows->length; $i++) {
                                $row = $rows->item($i);
                                $cells = $row->getElementsByTagName('td');
                                if ($cells->length > 0) {
                                    $first_cell = trim($cells->item(0)->nodeValue);
                                    if ($first_cell == 'No' || strtolower($first_cell) == 'no') {
                                        $header_found = true;
                                        $data_start = $i + 2; // Skip header dan sub-header
                                        break;
                                    }
                                }
                            }
                            
                            if ($header_found) {
                                $success_count = 0;
                                $skip_count = 0;
                                
                                // Proses data siswa
                                for ($i = $data_start; $i < $rows->length; $i++) {
                                    $row = $rows->item($i);
                                    $cells = $row->getElementsByTagName('td');
                                    
                                    if ($cells->length >= 11) {
                                        $nis = trim($cells->item(1)->nodeValue);
                                        $nama = trim($cells->item(2)->nodeValue);
                                        
                                        // Ambil nilai
                                        $f1 = trim($cells->item(3)->nodeValue);
                                        $f2 = trim($cells->item(4)->nodeValue);
                                        $f3 = trim($cells->item(5)->nodeValue);
                                        $f4 = trim($cells->item(6)->nodeValue);
                                        $sts = trim($cells->item(7)->nodeValue);
                                        $sas = trim($cells->item(8)->nodeValue);
                                        
                                        // Skip jika NIS kosong
                                        if (empty($nis)) continue;
                                        
                                        // Cari id_siswa
                                        $query_siswa = "SELECT id_siswa FROM siswa WHERE nis = '$nis' LIMIT 1";
                                        $result_siswa = mysqli_query($conn, $query_siswa);
                                        $siswa = mysqli_fetch_assoc($result_siswa);
                                        
                                        if ($siswa) {
                                            // Format nilai
                                            $f1 = ($f1 && $f1 != '-') ? "'$f1'" : "NULL";
                                            $f2 = ($f2 && $f2 != '-') ? "'$f2'" : "NULL";
                                            $f3 = ($f3 && $f3 != '-') ? "'$f3'" : "NULL";
                                            $f4 = ($f4 && $f4 != '-') ? "'$f4'" : "NULL";
                                            $sts = ($sts && $sts != '-') ? "'$sts'" : "NULL";
                                            $sas = ($sas && $sas != '-') ? "'$sas'" : "NULL";
                                            
                                            // Hitung nilai akhir
                                            $nilai_array = [];
                                            if ($f1 != "NULL") $nilai_array[] = str_replace("'", "", $f1);
                                            if ($f2 != "NULL") $nilai_array[] = str_replace("'", "", $f2);
                                            if ($f3 != "NULL") $nilai_array[] = str_replace("'", "", $f3);
                                            if ($f4 != "NULL") $nilai_array[] = str_replace("'", "", $f4);
                                            if ($sts != "NULL") $nilai_array[] = str_replace("'", "", $sts);
                                            if ($sas != "NULL") $nilai_array[] = str_replace("'", "", $sas);
                                            
                                            if (count($nilai_array) > 0) {
                                                $nilai_akhir = array_sum($nilai_array) / count($nilai_array);
                                                $predikat = generatePredikat($nilai_akhir);
                                                
                                                // Cek apakah sudah ada
                                                $check_nilai = "SELECT * FROM nilai 
                                                               WHERE id_siswa = {$siswa['id_siswa']} 
                                                               AND id_mapel = $id_mapel 
                                                               AND id_semester = $id_semester_file";
                                                $result_check_nilai = mysqli_query($conn, $check_nilai);
                                                
                                                if (mysqli_num_rows($result_check_nilai) > 0) {
                                                    // Update
                                                    $query_save = "UPDATE nilai SET 
                                                                  nilai_formatif_1 = $f1,
                                                                  nilai_formatif_2 = $f2,
                                                                  nilai_formatif_3 = $f3,
                                                                  nilai_formatif_4 = $f4,
                                                                  nilai_sts = $sts,
                                                                  nilai_sas = $sas,
                                                                  nilai_akhir = '$nilai_akhir',
                                                                  predikat = '$predikat',
                                                                  id_guru = $id_user
                                                                  WHERE id_siswa = {$siswa['id_siswa']} 
                                                                  AND id_mapel = $id_mapel 
                                                                  AND id_semester = $id_semester_file";
                                                } else {
                                                    // Insert
                                                    $query_save = "INSERT INTO nilai (id_siswa, id_mapel, id_semester, 
                                                                  nilai_formatif_1, nilai_formatif_2, nilai_formatif_3, nilai_formatif_4, 
                                                                  nilai_sts, nilai_sas, nilai_akhir, predikat, deskripsi, id_guru) 
                                                                  VALUES ({$siswa['id_siswa']}, $id_mapel, $id_semester_file, 
                                                                  $f1, $f2, $f3, $f4, $sts, $sas, '$nilai_akhir', '$predikat', '', $id_user)";
                                                }
                                                
                                                if (mysqli_query($conn, $query_save)) {
                                                    $success_count++;
                                                    $preview_data[] = [
                                                        'nis' => $nis,
                                                        'nama' => $nama,
                                                        'status' => 'Berhasil'
                                                    ];
                                                } else {
                                                    $preview_data[] = [
                                                        'nis' => $nis,
                                                        'nama' => $nama,
                                                        'status' => 'Gagal: ' . mysqli_error($conn)
                                                    ];
                                                }
                                            } else {
                                                $skip_count++;
                                            }
                                        } else {
                                            $preview_data[] = [
                                                'nis' => $nis,
                                                'nama' => $nama,
                                                'status' => 'Siswa tidak ditemukan'
                                            ];
                                        }
                                    }
                                }
                                
                                if ($success_count > 0) {
                                    $success = "Berhasil mengupload dan menyimpan $success_count nilai!";
                                    if ($skip_count > 0) {
                                        $success .= " ($skip_count data dilewati karena nilai kosong)";
                                    }
                                } else {
                                    $error = 'Tidak ada data yang berhasil disimpan!';
                                }
                            } else {
                                $error = 'Format file tidak sesuai! Header tabel tidak ditemukan.';
                            }
                        }
                    }
                }
            } else {
                $error = 'File tidak mengandung tabel data!';
            }
        }
    }
}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Nilai Excel - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Upload Nilai (Excel)</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h3>Upload File Excel Nilai</h3>
                </div>
                <div class="card-body">
                    <?php if ($semester_aktif): ?>
                        <p><strong>Semester Aktif:</strong> <?php echo $semester_aktif['nama_semester']; ?></p>
                        <br>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="file_excel">Pilih File Excel *</label>
                                <input type="file" id="file_excel" name="file_excel" class="form-control" 
                                       accept=".xls,.xlsx" required>
                                <small style="color: #666;">Format: .xls atau .xlsx (max 5MB)</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                📤 Upload dan Proses
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger">Tidak ada semester aktif. Hubungi administrator!</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($preview_data)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3>Hasil Upload</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    foreach ($preview_data as $data): 
                                        $status_class = strpos($data['status'], 'Berhasil') !== false ? 'success' : 'danger';
                                    ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $data['nis']; ?></td>
                                            <td><?php echo $data['nama']; ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $status_class; ?>">
                                                    <?php echo $data['status']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h3>Panduan Upload</h3>
                </div>
                <div class="card-body">
                    <h4>Langkah-langkah:</h4>
                    <ol>
                        <li>Download file Excel nilai dari menu <strong>"Download Excel"</strong></li>
                        <li>Buka file Excel tersebut</li>
                        <li>Isi nilai di kolom: <strong>F1, F2, F3, F4, STS, dan SAS</strong></li>
                        <li><strong>JANGAN</strong> mengubah struktur tabel atau header</li>
                        <li><strong>JANGAN</strong> menghapus informasi rombel, mapel, dan guru di bagian atas</li>
                        <li>Simpan file Excel</li>
                        <li>Upload kembali file Excel melalui form di atas</li>
                        <li>Sistem akan otomatis membaca dan menyimpan nilai ke database</li>
                    </ol>
                    
                    <h4>Kolom yang Dibaca:</h4>
                    <ul>
                        <li><strong>F1-F4:</strong> Nilai Formatif 1 sampai 4</li>
                        <li><strong>STS:</strong> Sumatif Tengah Semester</li>
                        <li><strong>SAS:</strong> Sumatif Akhir Semester</li>
                    </ul>
                    
                    <h4>Catatan Penting:</h4>
                    <ul>
                        <li>File Excel harus yang Anda download sendiri dari menu "Download Excel"</li>
                        <li>File mengandung metadata tersembunyi (ID Rombel, Mapel, Guru, Semester)</li>
                        <li>Sistem akan memverifikasi ID guru di file dengan akun yang login</li>
                        <li>Hanya file dari semester aktif yang bisa diupload</li>
                        <li>Nilai akhir akan dihitung otomatis (rata-rata dari nilai yang diisi)</li>
                        <li>Biarkan kolom kosong atau isi dengan "-" untuk nilai yang tidak ada</li>
                        <li>Data yang sudah ada akan di-update dengan data baru</li>
                    </ul>
                    
                    <h4>Tips:</h4>
                    <ul>
                        <li>✅ Gunakan format angka desimal dengan titik (contoh: 85.5)</li>
                        <li>✅ Pastikan NIS siswa tidak berubah</li>
                        <li>✅ Periksa kembali nilai sebelum upload</li>
                        <li>❌ Jangan mengubah nama siswa atau NIS</li>
                        <li>❌ Jangan menambah atau menghapus baris siswa</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
    </style>
</body>
</html>
