<?php
require_once '../../config.php';
//requireRole(['administrator', 'wali_kelas']);

$id_siswa = isset($_GET['id']) ? cleanInput($_GET['id']) : '';
$id_semester = isset($_GET['semester']) ? cleanInput($_GET['semester']) : '';

if(!$id_siswa) {
    die("ID Siswa tidak valid");
}

// Cek akses jika wali kelas
if($_SESSION['role'] == 'wali_kelas') {
    $id_wali_kelas = $_SESSION['user_id'];
    $query_check = "SELECT s.* FROM siswa s 
                    INNER JOIN rombel r ON s.id_rombel = r.id_rombel 
                    WHERE s.id_siswa = '$id_siswa' AND r.id_wali_kelas = '$id_wali_kelas'";
    $result_check = mysqli_query($conn, $query_check);
    if(mysqli_num_rows($result_check) == 0) {
        die("Anda tidak memiliki akses untuk melihat rapor siswa ini");
    }
}

// Ambil data siswa
$query_siswa = "SELECT s.*, r.nama_rombel, j.nama_jurusan, case when r.tingkat = 'X' then 'E' else 'F' end as fase,
                (SELECT nama_lengkap FROM users WHERE id_user = r.id_wali_kelas) as wali_kelas
                FROM siswa s
                INNER JOIN rombel r ON s.id_rombel = r.id_rombel
                INNER JOIN jurusan j ON r.id_jurusan = j.id_jurusan
                WHERE s.id_siswa = '$id_siswa'";
$result_siswa = mysqli_query($conn, $query_siswa);
$siswa = mysqli_fetch_assoc($result_siswa);

if(!$siswa) {
    die("Data siswa tidak ditemukan");
}

// Ambil data semester jika ada
$semester_info = '';
$tahun_ajaran = '';
if($id_semester) {
    $query_semester = "SELECT * FROM semester WHERE id_semester = '$id_semester'";
    $result_semester = mysqli_query($conn, $query_semester);
    $semester_data = mysqli_fetch_assoc($result_semester);
    if($semester_data) {
        $semester_info = $semester_data['nama_semester'];
        $tahun_ajaran = $semester_data['tahun_ajaran'];
    }
}

// Ambil nilai siswa per mata pelajaran
$where_semester = $id_semester ? "AND n.id_semester = '$id_semester'" : "";
$query_nilai = "SELECT m.nama_mapel,m.kelompok, n.nilai_akhir, m.deskripsi_a, m.deskripsi_b, m.deskripsi_c, m.deskripsi_d
                FROM nilai n
                INNER JOIN mata_pelajaran m ON n.id_mapel = m.id_mapel
                WHERE n.id_siswa = '$id_siswa' $where_semester
                ORDER BY m.kelompok, m.urutan, m.nama_mapel";
$result_nilai = mysqli_query($conn, $query_nilai);

// Ambil data rapor tambahan
$query_rapor = "SELECT * FROM rapor_tambahan WHERE id_siswa = '$id_siswa' AND id_semester = '$id_semester'";
$result_rapor = mysqli_query($conn, $query_rapor);
$rapor_tambahan = mysqli_fetch_assoc($result_rapor);

// Ambil data ekstrakurikuler
$query_ekskul = "SELECT * FROM rapor_ekstrakurikuler WHERE id_siswa = '$id_siswa' AND id_semester = '$id_semester' ORDER BY id_ekstrakurikuler";
$result_ekskul = mysqli_query($conn, $query_ekskul);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor - <?php echo htmlspecialchars($siswa['nama_lengkap']); ?></title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 20px;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .rapor-container {
            max-width: 210mm;
            margin: 0 auto;
            background-color: white;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 16pt;
            font-weight: bold;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 14pt;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        
        .info-label {
            width: 150px;
            font-weight: bold;
        }
        
        .info-separator {
            width: 20px;
            text-align: center;
        }
        
        .info-value {
            flex: 1;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table, th, td {
            border: 1px solid #000;
        }
        
        th {
            background-color: #f0f0f0;
            padding: 8px;
            text-align: center;
            font-weight: bold;
        }
        
        td {
            padding: 8px;
            vertical-align: top;
        }
        
        .text-center {
            text-align: center;
        }
        
        .section-title {
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 10px;
            font-size: 13pt;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        
        .signature-box {
            text-align: center;
            width: 30%;
        }
        
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        
        .btn-print {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .btn-print:hover {
            background-color: #45a049;
        }
        
        .btn-back {
            background-color: #2196F3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-back:hover {
            background-color: #1976D2;
        }
        
        .kokurikuler-table {
            margin-bottom: 20px;
        }
        
        .kehadiran-table {
            width: 40%;
        }
        
        .catatan-box {
            border: 1px solid #000;
            padding: 10px;
            min-height: 80px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <a href="javascript:history.back()" class="btn-back">← Kembali</a>
        <button onclick="window.print()" class="btn-print">🖨️ Cetak / Download PDF</button>
    </div>
    
    <div class="rapor-container">
        <div class="header">
            <h1>LAPORAN HASIL BELAJAR SUMATIF AKHIR SEMESTER GANJIL<br>
                TAHUN AJARAN 2025 - 2026
            </h1>
        </div>
        
        <div class="info-section">
            <div style="display: flex; justify-content: space-between;">
                <div style="width: 48%;">
                    <div class="info-row">
                        <div class="info-label">Nama Murid</div>
                        <div class="info-separator">:</div>
                        <div class="info-value"><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">NISN</div>
                        <div class="info-separator">:</div>
                        <div class="info-value"><?php echo htmlspecialchars($siswa['nis']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Sekolah</div>
                        <div class="info-separator">:</div>
                        <div class="info-value">SMK</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Alamat</div>
                        <div class="info-separator">:</div>
                        <div class="info-value"><?php echo htmlspecialchars($siswa['alamat']); ?></div>
                    </div>
                </div>
                <div style="width: 48%;">
                    <div class="info-row">
                        <div class="info-label">Kelas</div>
                        <div class="info-separator">:</div>
                        <div class="info-value"><?php echo htmlspecialchars($siswa['nama_rombel']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Fase</div>
                        <div class="info-separator">:</div>
                        <div class="info-value"><?php echo htmlspecialchars($siswa['fase'] ?: '-'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Semester</div>
                        <div class="info-separator">:</div>
                        <div class="info-value"><?php echo htmlspecialchars($semester_info ?: '-'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Tahun Ajaran</div>
                        <div class="info-separator">:</div>
                        <div class="info-value"><?php echo htmlspecialchars($tahun_ajaran ?: '-'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 30%;">Mata Pelajaran</th>
                    <th style="width: 10%;">Nilai<br>Akhir</th>
                    <th style="width: 55%;">Capaian Kompetensi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $kelompok = "";
                $no = 1;
                if(mysqli_num_rows($result_nilai) > 0):
                    while($nilai = mysqli_fetch_assoc($result_nilai)): 
                        if($kelompok != $nilai['kelompok']) {
                            $kelompok = $nilai['kelompok'];
                            if ($kelompok == 'A') {
                                $kelompok_nama = 'Kelompok Mata Pelajaran Umum';
                            } elseif ($kelompok == 'B') {
                                $kelompok_nama = 'Kelompok Mata Pelajaran Kejuruan';
                            } else {
                                $kelompok_nama = 'Muatan Lokal';
                            }
                            echo '<tr><td colspan="4" style="font-weight: bold; background-color: #e0e0e0;">' . htmlspecialchars(ucwords($kelompok_nama)) . '</td></tr>';
                        }
                ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($nilai['nama_mapel']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($nilai['nilai_akhir']); ?></td>
                    <td>
                        <?php 
                            if ($nilai['nilai_akhir'] >= 90) {
                                echo nl2br(htmlspecialchars($nilai['deskripsi_a'] ?: '-'));
                            } elseif ($nilai['nilai_akhir'] >= 80) {
                                echo nl2br(htmlspecialchars($nilai['deskripsi_b'] ?: '-'));
                            } elseif ($nilai['nilai_akhir'] >= 70) {
                                echo nl2br(htmlspecialchars($nilai['deskripsi_c'] ?: '-'));
                            } else {
                                echo nl2br(htmlspecialchars($nilai['deskripsi_d'] ?: '-'));
                            } 
                        ?>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="4" class="text-center">Belum ada data nilai</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <table class="kokurikuler-table">
            <thead>
                <tr>
                    <th style="width: 100%;">Kokurikuler</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php if($rapor_tambahan && !empty($rapor_tambahan['deskripsi_kokurikuler'])): ?>
                    <p style="margin-bottom: 10px;"><?php echo nl2br(htmlspecialchars($rapor_tambahan['deskripsi_kokurikuler'])); ?></p>
                    <?php else: ?>
                    <p style="margin-bottom: 10px; color: #999; font-style: italic;">Belum ada deskripsi kokurikuler.</p>
                    <?php endif; ?></td>
                   
                </tr>
            </tbody>
        </table>
        
        <table class="kokurikuler-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 35%;">Ekstrakurikuler</th>
                    <th style="width: 60%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if(mysqli_num_rows($result_ekskul) > 0):
                    while($ekskul = mysqli_fetch_assoc($result_ekskul)): 
                ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($ekskul['nama_ekstrakurikuler']); ?></td>
                    <td><?php echo htmlspecialchars($ekskul['keterangan']); ?></td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="3" class="text-center" style="color: #999; font-style: italic;">Belum ada data ekstrakurikuler</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <div style="width: 100%;">
                <div class="section-title">Ketidakhadiran</div>
                <table class="kehadiran-table">
                    <tr>
                        <td>Sakit</td>
                        <td style="text-align: center;"><?php echo $rapor_tambahan ? htmlspecialchars($rapor_tambahan['sakit']) : '0'; ?> hari</td>
                    </tr>
                    <tr>
                        <td>Izin</td>
                        <td style="text-align: center;"><?php echo $rapor_tambahan ? htmlspecialchars($rapor_tambahan['izin']) : '0'; ?> hari</td>
                    </tr>
                    <tr>
                        <td>Tanpa Keterangan</td>
                        <td style="text-align: center;"><?php echo $rapor_tambahan ? htmlspecialchars($rapor_tambahan['tanpa_keterangan']) : '0'; ?> hari</td>
                    </tr>
                </table>
            </div>
            <div style="width: 48%;">
                <div class="section-title">Catatan Wali Kelas</div>
                <div class="catatan-box">
                    <?php if($rapor_tambahan && !empty($rapor_tambahan['catatan_wali_kelas'])): ?>
                        <?php echo nl2br(htmlspecialchars($rapor_tambahan['catatan_wali_kelas'])); ?>
                    <?php else: ?>
                        <span style="color: #999; font-style: italic;">Belum ada catatan dari wali kelas.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <table class="kokurikuler-table">
            <thead>
                <tr>
                    <th style="width: 100%;">Tanggapan Orang Tua/Wali Murid</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><br><br><br></td>
                   
                </tr>
            </tbody>
        </table>
        <div class="signature-section">
            <div class="signature-box">
            </div>
            <div class="signature-box">
            </div>
            <div class="signature-box">
                <div>Malang, .....................<?= date("Y") ?></div>
            </div>
        </div>
        <div class="signature-section">
            <div class="signature-box">
                <div>Orang Tua Murid<br><br></div>
                <div class="signature-line"></div>
            </div>
            <div class="signature-box">
                <div><br><br><br>Kepala Sekolah<br><br></div>
                <div class="signature-line"></div>
            </div>
            <div class="signature-box">
                <div>Wali Kelas<br><br></div>
                <div class="signature-line"></div>
            </div>
        </div>
    </div>
</body>
</html>
