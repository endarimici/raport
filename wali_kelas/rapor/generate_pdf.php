<?php
require_once '../../config.php';
require_once('../../fpdf.php');

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

// Ambil data semester
$semester_info = '';
$tahun_ajaran = '';
$jenis_semester = 'GANJIL';
if($id_semester) {
    $query_semester = "SELECT * FROM semester WHERE id_semester = '$id_semester'";
    $result_semester = mysqli_query($conn, $query_semester);
    $semester_data = mysqli_fetch_assoc($result_semester);
    if($semester_data) {
        $semester_info = $semester_data['nama_semester'];
        $tahun_ajaran = $semester_data['tahun_ajaran'];
        $jenis_semester = strtoupper($semester_data['semester']);
    }
}

// Ambil nilai siswa
$where_semester = $id_semester ? "AND n.id_semester = '$id_semester'" : "";
$query_nilai = "SELECT m.nama_mapel, m.kelompok, n.nilai_akhir, m.deskripsi_a, m.deskripsi_b, m.deskripsi_c, m.deskripsi_d
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

// Buat PDF
class PDF extends FPDF
{
    function Header()
    {
        // Header kosong, akan dibuat manual
    }
    
    function Footer()
    {
        // Footer kosong
    }
    
    function MultiCellRow($w, $h, $texts, $border = 0, $align = 'L', $fill = false)
    {
        // Save current position
        $x = $this->GetX();
        $y = $this->GetY();
        
        $max_height = 0;
        
        // Calculate heights
        $heights = array();
        foreach($texts as $i => $text) {
            $this->SetXY($x + array_sum(array_slice($w, 0, $i)), $y);
            $nb = $this->NbLines($w[$i], $text);
            $height = $h * $nb;
            $heights[] = $height;
            if($height > $max_height) {
                $max_height = $height;
            }
        }
        
        // Draw cells
        foreach($texts as $i => $text) {
            $this->SetXY($x + array_sum(array_slice($w, 0, $i)), $y);
            $this->MultiCell($w[$i], $h, $text, $border, $align[$i], $fill);
        }
        
        // Draw borders if needed
        if($border) {
            $this->SetXY($x, $y);
            $total_width = array_sum($w);
            $this->Rect($x, $y, $total_width, $max_height);
            
            $current_x = $x;
            foreach($w as $width) {
                $this->Line($current_x, $y, $current_x, $y + $max_height);
                $current_x += $width;
            }
            $this->Line($current_x, $y, $current_x, $y + $max_height);
        }
        
        // Set position after the row
        $this->SetXY($x, $y + $max_height);
        
        return $max_height;
    }
    
    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if($nb > 0 && $s[$nb-1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i < $nb)
        {
            $c = $s[$i];
            if($c == "\n")
            {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if($l > $wmax)
            {
                if($sep == -1)
                {
                    if($i == $j)
                        $i++;
                }
                else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);
$pdf->SetLineWidth(0.3); // Set border tipis

// Header
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 7, 'LAPORAN HASIL BELAJAR SUMATIF AKHIR SEMESTER ' . $jenis_semester, 0, 1, 'C');
$pdf->Cell(0, 7, 'TAHUN AJARAN ' . $tahun_ajaran, 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetLineWidth(0.2);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(5);

// Info Siswa
$pdf->SetFont('Arial', '', 10);

// Kolom kiri
$y_start = $pdf->GetY();
$x_left = 15;
$x_right = 110;

// Nama Murid
$pdf->SetXY($x_left, $y_start);
$pdf->Cell(30, 5, 'Nama Murid', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->MultiCell(62, 5, $siswa['nama_lengkap'], 0, 'L');

// NISN
$pdf->SetX($x_left);
$pdf->Cell(30, 5, 'NISN', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(62, 5, $siswa['nis'], 0, 1);

// Sekolah
$pdf->SetX($x_left);
$pdf->Cell(30, 5, 'Sekolah', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$y_sekolah = $pdf->GetY();
$pdf->MultiCell(62, 5, 'SMK MUHAMMADIYAH 8 PAKIS', 0, 'L');

// Alamat
$pdf->SetX($x_left);
$pdf->Cell(30, 5, 'Alamat', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->MultiCell(62, 5, 'JL RAYA SUMBERPASIR NO 188', 0, 'L');

$y_left_end = $pdf->GetY();

// Kolom kanan
$pdf->SetXY($x_right, $y_start);
$pdf->Cell(30, 5, 'Kelas', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(52, 5, $siswa['nama_rombel'], 0, 1);

$pdf->SetX($x_right);
$pdf->Cell(30, 5, 'Fase', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(52, 5, $siswa['fase'], 0, 1);

$pdf->SetX($x_right);
$pdf->Cell(30, 5, 'Semester', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->MultiCell(52, 5, $semester_info, 0, 'L');

$pdf->SetX($x_right);
$pdf->Cell(30, 5, 'Tahun Ajaran', 0, 0);
$pdf->Cell(3, 5, ':', 0, 0);
$pdf->Cell(52, 5, $tahun_ajaran, 0, 1);

$y_right_end = $pdf->GetY();

$pdf->SetY(max($y_left_end, $y_right_end));
$pdf->Ln(3);

// Tabel Nilai
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(10, 7, 'No.', 1, 0, 'C');  
$pdf->Cell(55, 7, 'Mata Pelajaran', 1, 0, 'C');
$pdf->Cell(20, 7, 'Nilai Akhir', 1, 0, 'C');
$pdf->Cell(95, 7, 'Capaian Kompetensi', 1, 1, 'C');

$pdf->SetFont('Arial', '', 9);
$no = 1;
$kelompok = "";

while($nilai = mysqli_fetch_assoc($result_nilai)) {
    // Header kelompok
    if($kelompok != $nilai['kelompok']) {
        $kelompok = $nilai['kelompok'];
        if ($kelompok == 'A') {
            $kelompok_nama = 'Kelompok Mata Pelajaran Umum';
        } elseif ($kelompok == 'B') {
            $kelompok_nama = 'Kelompok Mata Pelajaran Kejuruan';
        } else {
            $kelompok_nama = 'Muatan Lokal';
        }
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(180, 6, $kelompok_nama, 1, 1, 'L', true);
        $pdf->SetFont('Arial', '', 9);
    }
    
    // Tentukan deskripsi berdasarkan nilai
    $deskripsi = '-';
    if ($nilai['nilai_akhir'] >= 90) {
        $deskripsi = $nilai['deskripsi_a'] ?: '-';
    } elseif ($nilai['nilai_akhir'] >= 80) {
        $deskripsi = $nilai['deskripsi_b'] ?: '-';
    } elseif ($nilai['nilai_akhir'] >= 70) {
        $deskripsi = $nilai['deskripsi_c'] ?: '-';
    } else {
        $deskripsi = $nilai['deskripsi_d'] ?: '-';
    }
    
    // Hitung tinggi baris untuk semua kolom
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $yAwal = $y;
    // Hitung jumlah baris untuk nama mapel dan deskripsi
    $nb_mapel = $pdf->NbLines(50, $nilai['nama_mapel']);
    $nb_deskripsi = $pdf->NbLines(95, $deskripsi);
    $nb_max = max($nb_mapel, $nb_deskripsi);
    $h = 5 * $nb_max;
    if($h < 6) $h = 6;
    
    // No
    $pdf->Cell(10, $h, $no++, 1, 0, 'C');
    
    // Mata Pelajaran dengan MultiCell
    $x_after_no = $pdf->GetX();
    $pdf->MultiCell(50, 5, $nilai['nama_mapel'], 0, 'L');
    $yAkhir = $pdf->GetY();
    // Kembali ke posisi setelah no, lalu geser untuk nilai akhir
    $pdf->SetXY($x_after_no + 55, $y);
    $pdf->Cell(20, $h, number_format($nilai['nilai_akhir'], 2), 1, 0, 'C');
    
    // MultiCell untuk deskripsi
    $pdf->SetXY($x_after_no + 75, $y);
    $pdf->MultiCell(95, 5, $deskripsi, 0, 'L');
    if ($pdf->GetY() > $yAkhir) {
        $yAkhir = $pdf->GetY();
    }
    $pdf->Line($x,$yAkhir,$x+180,$yAkhir);
    $pdf->Line($x+180,$yAwal,$x+180,$yAkhir);
    // Set posisi Y setelah baris
    $pdf->SetY($y + $h);
}

$pdf->Ln(3);

// Kokurikuler
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(180, 6, 'Kokurikuler', 1, 1, 'C', true);
$pdf->SetFont('Arial', '', 9);
$kokurikuler = $rapor_tambahan && !empty($rapor_tambahan['deskripsi_kokurikuler']) ? 
               $rapor_tambahan['deskripsi_kokurikuler'] : 'Belum ada deskripsi kokurikuler.';
$pdf->MultiCell(180, 5, $kokurikuler, 1);

$pdf->Ln(2);

// Ekstrakurikuler
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(10, 6, 'No.', 1, 0, 'C');
$pdf->Cell(70, 6, 'Ekstrakurikuler', 1, 0, 'C');
$pdf->Cell(100, 6, 'Keterangan', 1, 1, 'C');

$pdf->SetFont('Arial', '', 9);
$no_ekskul = 1;
if(mysqli_num_rows($result_ekskul) > 0) {
    while($ekskul = mysqli_fetch_assoc($result_ekskul)) {
        $pdf->Cell(10, 6, $no_ekskul++, 1, 0, 'C');
        $pdf->Cell(70, 6, $ekskul['nama_ekstrakurikuler'], 1, 0, 'L');
        $pdf->Cell(100, 6, $ekskul['keterangan'], 1, 1, 'L');
    }
} else {
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->Cell(180, 6, 'Belum ada data ekstrakurikuler', 1, 1, 'C');
}

$pdf->Ln(3);

// Ketidakhadiran dan Catatan
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(90, 6, 'Ketidakhadiran', 0, 0);
$pdf->Cell(90, 6, 'Catatan Wali Kelas', 0, 1);

$pdf->SetFont('Arial', '', 9);
$y_start = $pdf->GetY();

// Ketidakhadiran
$pdf->Cell(45, 6, 'Sakit', 1);
$pdf->Cell(45, 6, ($rapor_tambahan ? $rapor_tambahan['sakit'] : '0') . ' hari', 1, 1, 'C');
$pdf->Cell(45, 6, 'Izin', 1);
$pdf->Cell(45, 6, ($rapor_tambahan ? $rapor_tambahan['izin'] : '0') . ' hari', 1, 1, 'C');
$pdf->Cell(45, 6, 'Tanpa Keterangan', 1);
$pdf->Cell(45, 6, ($rapor_tambahan ? $rapor_tambahan['tanpa_keterangan'] : '0') . ' hari', 1, 1, 'C');

$y_kehadiran = $pdf->GetY();

// Catatan Wali Kelas
//$pdf->SetY($y_start);
$pdf->SetX(105);
$catatan = $rapor_tambahan && !empty($rapor_tambahan['catatan_wali_kelas']) ? 
           $rapor_tambahan['catatan_wali_kelas'] : 'Belum ada catatan dari wali kelas.';
$pdf->MultiCell(90, 5, $catatan, 1);

$y_catatan = $pdf->GetY();
$pdf->SetY(max($y_kehadiran, $y_catatan));

$pdf->Ln(3);

// Tanggapan Orang Tua
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(180, 6, 'Tanggapan Orang Tua/Wali Murid', 1, 1, 'C', true);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(180, 15, '', 1, 1);

$pdf->Ln(3);

// Tanda Tangan
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(60, 5, '', 0, 0);
$pdf->Cell(60, 5, '', 0, 0);
$pdf->Cell(60, 5, 'Malang, .................. ' . date('Y'), 0, 1, 'C');

$pdf->Ln(2);

$pdf->Cell(60, 5, 'Orang Tua Murid', 0, 0, 'C');
$pdf->Cell(60, 5, 'Kepala Sekolah', 0, 0, 'C');
$pdf->Cell(60, 5, 'Wali Kelas', 0, 1, 'C');

$pdf->Ln(20);

$pdf->Cell(60, 5, '____________________', 0, 0, 'C');
$pdf->Cell(60, 5, '____________________', 0, 0, 'C');
$pdf->Cell(60, 5, '____________________', 0, 1, 'C');

// Output PDF
$filename = 'Rapor_' . preg_replace("/[^a-zA-Z0-9]/", "_", $siswa['nama_lengkap']) . '_' . 
            preg_replace("/[^a-zA-Z0-9]/", "_", $semester_info) . '.pdf';
$pdf->Output('D', $filename);
