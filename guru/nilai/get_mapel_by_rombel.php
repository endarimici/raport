<?php
require_once '../../config.php';
requireRole('guru');

header('Content-Type: application/json');

$id_user = $_SESSION['user_id'];
$id_rombel = isset($_GET['id_rombel']) ? cleanInput($_GET['id_rombel']) : '';

$response = ['success' => false, 'data' => []];

if ($id_rombel) {
    // Ambil mata pelajaran yang diajar guru di rombel tertentu
    $query = "SELECT DISTINCT m.id_mapel, m.kode_mapel, m.nama_mapel
              FROM mapel_guru mg
              INNER JOIN mata_pelajaran m ON mg.id_mapel = m.id_mapel
              WHERE mg.id_user = $id_user 
              AND mg.id_rombel = '$id_rombel'
              ORDER BY m.nama_mapel";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $mapel_list = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $mapel_list[] = [
                'id_mapel' => $row['id_mapel'],
                'kode_mapel' => $row['kode_mapel'],
                'nama_mapel' => $row['nama_mapel']
            ];
        }
        
        $response['success'] = true;
        $response['data'] = $mapel_list;
    }
}

echo json_encode($response);
