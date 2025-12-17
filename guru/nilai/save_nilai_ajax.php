<?php
require_once '../../config.php';
requireRole('guru');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_siswa = cleanInput($_POST['id_siswa']);
    $id_mapel = cleanInput($_POST['id_mapel']);
    $id_semester = cleanInput($_POST['id_semester']);
    $field = cleanInput($_POST['field']);
    $value = isset($_POST['value']) && $_POST['value'] !== '' ? cleanInput($_POST['value']) : null;
    $id_user = $_SESSION['user_id'];
    
    // Validasi nilai
    if ($value !== null && ($value < 0 || $value > 100)) {
        echo json_encode(['success' => false, 'message' => 'Nilai harus antara 0-100']);
        exit();
    }
    
    // Mapping field name ke kolom database
    $field_mapping = [
        'formatif_1' => 'nilai_formatif_1',
        'formatif_2' => 'nilai_formatif_2',
        'formatif_3' => 'nilai_formatif_3',
        'formatif_4' => 'nilai_formatif_4',
        'sts' => 'nilai_sts',
        'sas' => 'nilai_sas',
        'deskripsi' => 'deskripsi'
    ];
    
    if (!isset($field_mapping[$field])) {
        echo json_encode(['success' => false, 'message' => 'Field tidak valid']);
        exit();
    }
    
    $db_field = $field_mapping[$field];
    
    // Cek apakah record sudah ada
    $check = "SELECT * FROM nilai 
              WHERE id_siswa = $id_siswa 
              AND id_mapel = '$id_mapel' 
              AND id_semester = $id_semester";
    $result_check = mysqli_query($conn, $check);
    
    if (mysqli_num_rows($result_check) > 0) {
        // Update field tertentu
        $nilai_data = mysqli_fetch_assoc($result_check);
        
        // Update field
        $query = "UPDATE nilai SET 
                  $db_field = " . ($value !== null ? "'$value'" : "NULL") . "
                  WHERE id_siswa = $id_siswa 
                  AND id_mapel = '$id_mapel' 
                  AND id_semester = $id_semester";
        
        if (mysqli_query($conn, $query)) {
            // Hitung ulang nilai akhir
            $query_nilai = "SELECT * FROM nilai 
                           WHERE id_siswa = $id_siswa 
                           AND id_mapel = '$id_mapel' 
                           AND id_semester = $id_semester";
            $result_nilai = mysqli_query($conn, $query_nilai);
            $nilai = mysqli_fetch_assoc($result_nilai);
            
            $nilai_array = array_filter([
                $nilai['nilai_formatif_1'],
                $nilai['nilai_formatif_2'],
                $nilai['nilai_formatif_3'],
                $nilai['nilai_formatif_4'],
                $nilai['nilai_sts'],
                $nilai['nilai_sas']
            ], function($v) { return $v !== null && $v !== ''; });
            
            if (count($nilai_array) > 0) {
                $nilai_akhir = array_sum($nilai_array) / count($nilai_array);
                $predikat = generatePredikat($nilai_akhir);
                
                // Update nilai akhir dan predikat
                $query_update = "UPDATE nilai SET 
                                nilai_akhir = '$nilai_akhir',
                                predikat = '$predikat'
                                WHERE id_siswa = $id_siswa 
                                AND id_mapel = '$id_mapel' 
                                AND id_semester = $id_semester";
                mysqli_query($conn, $query_update);
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Nilai berhasil disimpan',
                    'rata_rata' => number_format($nilai_akhir, 2)
                ]);
            } else {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Nilai berhasil disimpan',
                    'rata_rata' => ''
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan nilai: ' . mysqli_error($conn)]);
        }
    } else {
        // Insert record baru
        $query = "INSERT INTO nilai (id_siswa, id_mapel, id_semester, $db_field, id_guru) 
                  VALUES ($id_siswa, '$id_mapel', $id_semester, " . ($value !== null ? "'$value'" : "NULL") . ", $id_user)";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode([
                'success' => true, 
                'message' => 'Nilai berhasil disimpan',
                'rata_rata' => ''
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan nilai: ' . mysqli_error($conn)]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
