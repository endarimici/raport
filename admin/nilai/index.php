<?php
require_once '../../config.php';
requireRole('administrator');

// Filter
$filter_rombel = isset($_GET['rombel']) ? cleanInput($_GET['rombel']) : '';
$filter_mapel = isset($_GET['mapel']) ? cleanInput($_GET['mapel']) : '';
$filter_semester = isset($_GET['semester']) ? cleanInput($_GET['semester']) : '';

// Ambil data untuk filter
$query_rombel = "SELECT * FROM rombel ORDER BY nama_rombel";
$result_rombel_filter = mysqli_query($conn, $query_rombel);

$query_mapel = "SELECT * FROM mata_pelajaran ORDER BY nama_mapel";
$result_mapel_filter = mysqli_query($conn, $query_mapel);

$query_semester = "SELECT * FROM semester ORDER BY id_semester DESC";
$result_semester_filter = mysqli_query($conn, $query_semester);

// Query nilai dengan filter
$where = "WHERE 1=1";
if ($filter_rombel) $where .= " AND s.id_rombel = '$filter_rombel'";
if ($filter_mapel) $where .= " AND n.id_mapel = '$filter_mapel'";
if ($filter_semester) $where .= " AND n.id_semester = '$filter_semester'";

$query = "SELECT n.*, s.nis, s.nama_lengkap, m.nama_mapel, sm.nama_semester, u.nama_lengkap as guru
          FROM nilai n
          INNER JOIN siswa s ON n.id_siswa = s.id_siswa
          INNER JOIN mata_pelajaran m ON n.id_mapel = m.id_mapel
          INNER JOIN semester sm ON n.id_semester = sm.id_semester
          INNER JOIN users u ON n.id_guru = u.id_user
          $where
          ORDER BY n.id_nilai DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Nilai - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Data Nilai Siswa</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Filter Data Nilai</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="form-inline">
                        <div class="form-group">
                            <label for="rombel">Rombel</label>
                            <select id="rombel" name="rombel" class="form-control">
                                <option value="">-- Semua --</option>
                                <?php while ($r = mysqli_fetch_assoc($result_rombel_filter)): ?>
                                    <option value="<?php echo $r['id_rombel']; ?>" 
                                        <?php echo $filter_rombel == $r['id_rombel'] ? 'selected' : ''; ?>>
                                        <?php echo $r['nama_rombel']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="mapel">Mata Pelajaran</label>
                            <select id="mapel" name="mapel" class="form-control">
                                <option value="">-- Semua --</option>
                                <?php while ($m = mysqli_fetch_assoc($result_mapel_filter)): ?>
                                    <option value="<?php echo $m['id_mapel']; ?>" 
                                        <?php echo $filter_mapel == $m['id_mapel'] ? 'selected' : ''; ?>>
                                        <?php echo $m['nama_mapel']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="semester">Semester</label>
                            <select id="semester" name="semester" class="form-control">
                                <option value="">-- Semua --</option>
                                <?php while ($sem = mysqli_fetch_assoc($result_semester_filter)): ?>
                                    <option value="<?php echo $sem['id_semester']; ?>" 
                                        <?php echo $filter_semester == $sem['id_semester'] ? 'selected' : ''; ?>>
                                        <?php echo $sem['nama_semester']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="index.php" class="btn btn-secondary">Reset</a>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Nilai</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th rowspan="2">No</th>
                                    <th rowspan="2">NIS</th>
                                    <th rowspan="2">Nama Siswa</th>
                                    <th rowspan="2">Mata Pelajaran</th>
                                    <th rowspan="2">Semester</th>
                                    <th colspan="4" style="text-align:center;">Formatif</th>
                                    <th rowspan="2">STS</th>
                                    <th rowspan="2">SAS</th>
                                    <th rowspan="2">N. Akhir</th>
                                    <th rowspan="2">Predikat</th>
                                    <th rowspan="2">Status KKM</th>
                                    <th rowspan="2">Guru</th>
                                </tr>
                                <tr>
                                    <th style="text-align:center;">F1</th>
                                    <th style="text-align:center;">F2</th>
                                    <th style="text-align:center;">F3</th>
                                    <th style="text-align:center;">F4</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)): 
                                    // Ambil KKM mata pelajaran
                                    $query_kkm = "SELECT kkm FROM mata_pelajaran WHERE id_mapel = {$row['id_mapel']}";
                                    $result_kkm = mysqli_query($conn, $query_kkm);
                                    $kkm = mysqli_fetch_assoc($result_kkm)['kkm'];
                                    
                                    // Status KKM
                                    $status_kkm = '';
                                    if ($row['nilai_akhir'] < $kkm) {
                                        $status_kkm = '<span style="color:red;font-weight:bold;">⚠️ Di Bawah KKM</span>';
                                    } else {
                                        $status_kkm = '<span style="color:green;font-weight:bold;">✓ Tuntas</span>';
                                    }
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo $row['nis']; ?></td>
                                    <td><?php echo $row['nama_lengkap']; ?></td>
                                    <td><?php echo $row['nama_mapel']; ?></td>
                                    <td><?php echo $row['nama_semester']; ?></td>
                                    <td style="text-align:center;"><?php echo $row['nilai_formatif_1'] ?? '-'; ?></td>
                                    <td style="text-align:center;"><?php echo $row['nilai_formatif_2'] ?? '-'; ?></td>
                                    <td style="text-align:center;"><?php echo $row['nilai_formatif_3'] ?? '-'; ?></td>
                                    <td style="text-align:center;"><?php echo $row['nilai_formatif_4'] ?? '-'; ?></td>
                                    <td style="text-align:center;"><?php echo $row['nilai_sts'] ?? '-'; ?></td>
                                    <td style="text-align:center;"><?php echo $row['nilai_sas'] ?? '-'; ?></td>
                                    <td style="text-align:center;font-weight:bold;"><?php echo number_format($row['nilai_akhir'], 2); ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $row['predikat'] == 'A' ? 'success' : 
                                                ($row['predikat'] == 'B' ? 'info' : 
                                                ($row['predikat'] == 'C' ? 'warning' : 'danger')); 
                                        ?>">
                                            <?php echo $row['predikat']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $status_kkm; ?></td>
                                    <td><?php echo $row['guru']; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
