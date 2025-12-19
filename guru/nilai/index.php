<?php
require_once '../../config.php';
requireRole('guru');

$id_user = $_SESSION['user_id'];

// Ambil data semester aktif
$query_semester = "SELECT * FROM semester WHERE status = 'aktif' LIMIT 1";
$result_semester = mysqli_query($conn, $query_semester);
$semester_aktif = mysqli_fetch_assoc($result_semester);

if (!$semester_aktif) {
    $error_semester = "Tidak ada semester aktif. Hubungi administrator!";
}

// Ambil rombel dan mapel yang diajar guru
$query_mapel_guru = "SELECT DISTINCT r.id_rombel, r.nama_rombel, m.id_mapel, m.kode_mapel, m.nama_mapel
                     FROM mapel_guru mg
                     INNER JOIN rombel r ON mg.id_rombel = r.id_rombel
                     INNER JOIN mata_pelajaran m ON mg.id_mapel = m.id_mapel
                     WHERE mg.id_user = $id_user";
$result_mapel_guru = mysqli_query($conn, $query_mapel_guru);

// Filter
$filter_rombel = isset($_GET['rombel']) ? cleanInput($_GET['rombel']) : '';
$filter_mapel = isset($_GET['mapel']) ? cleanInput($_GET['mapel']) : '';

// Ambil data siswa berdasarkan filter
$siswa_list = [];
if ($filter_rombel && $filter_mapel && $semester_aktif) {
    $query_siswa = "SELECT s.* FROM siswa s 
                    WHERE s.id_rombel = '$filter_rombel' AND s.status = 'aktif'
                    ORDER BY s.nama_lengkap";
    $result_siswa = mysqli_query($conn, $query_siswa);
    
    while ($siswa = mysqli_fetch_assoc($result_siswa)) {
        // Cek apakah sudah ada nilai
        $query_nilai = "SELECT * FROM nilai 
                        WHERE id_siswa = {$siswa['id_siswa']} 
                        AND id_mapel = '$filter_mapel' 
                        AND id_semester = {$semester_aktif['id_semester']}";
        $result_nilai = mysqli_query($conn, $query_nilai);
        $nilai = mysqli_fetch_assoc($result_nilai);
        
        $siswa['nilai'] = $nilai;
        $siswa_list[] = $siswa;
    }
}

// Proses input/update nilai
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_nilai'])) {
    $success_count = 0;
    
    foreach ($_POST['siswa'] as $id_siswa => $data) {
        $formatif_1 = isset($data['formatif_1']) ? cleanInput($data['formatif_1']) : null;
        $formatif_2 = isset($data['formatif_2']) ? cleanInput($data['formatif_2']) : null;
        $formatif_3 = isset($data['formatif_3']) ? cleanInput($data['formatif_3']) : null;
        $formatif_4 = isset($data['formatif_4']) ? cleanInput($data['formatif_4']) : null;
        $sts = isset($data['sts']) ? cleanInput($data['sts']) : null;
        $sas = isset($data['sas']) ? cleanInput($data['sas']) : null;
        
        // Hitung nilai yang sudah diisi
        $nilai_array = array_filter([$formatif_1, $formatif_2, $formatif_3, $formatif_4, $sts, $sas], function($v) {
            return $v !== null && $v !== '';
        });
        
        if (count($nilai_array) > 0) {
            // Hitung nilai akhir (rata-rata dari nilai yang diisi)
            $nilai_akhir = array_sum($nilai_array) / count($nilai_array);
            $predikat = generatePredikat($nilai_akhir);
            
            // Ambil KKM mata pelajaran untuk pengecekan
            $query_kkm = "SELECT kkm FROM mata_pelajaran WHERE id_mapel = '$filter_mapel'";
            $result_kkm = mysqli_query($conn, $query_kkm);
            $kkm = mysqli_fetch_assoc($result_kkm)['kkm'];
            
            // Cek apakah sudah ada nilai
            $check = "SELECT * FROM nilai 
                      WHERE id_siswa = $id_siswa 
                      AND id_mapel = '$filter_mapel' 
                      AND id_semester = {$semester_aktif['id_semester']}";
            $result_check = mysqli_query($conn, $check);
            
            if (mysqli_num_rows($result_check) > 0) {
                // Update
                $query = "UPDATE nilai SET 
                          nilai_formatif_1 = " . ($formatif_1 ? "'$formatif_1'" : "NULL") . ",
                          nilai_formatif_2 = " . ($formatif_2 ? "'$formatif_2'" : "NULL") . ",
                          nilai_formatif_3 = " . ($formatif_3 ? "'$formatif_3'" : "NULL") . ",
                          nilai_formatif_4 = " . ($formatif_4 ? "'$formatif_4'" : "NULL") . ",
                          nilai_sts = " . ($sts ? "'$sts'" : "NULL") . ",
                          nilai_sas = " . ($sas ? "'$sas'" : "NULL") . ",
                          nilai_akhir = '$nilai_akhir',
                          predikat = '$predikat'
                          WHERE id_siswa = $id_siswa 
                          AND id_mapel = '$filter_mapel' 
                          AND id_semester = {$semester_aktif['id_semester']}";
            } else {
                // Insert
                $query = "INSERT INTO nilai (id_siswa, id_mapel, id_semester, nilai_formatif_1, nilai_formatif_2, nilai_formatif_3, nilai_formatif_4, nilai_sts, nilai_sas, nilai_akhir, predikat, id_guru) 
                          VALUES ($id_siswa, '$filter_mapel', {$semester_aktif['id_semester']}, 
                          " . ($formatif_1 ? "'$formatif_1'" : "NULL") . ", 
                          " . ($formatif_2 ? "'$formatif_2'" : "NULL") . ", 
                          " . ($formatif_3 ? "'$formatif_3'" : "NULL") . ", 
                          " . ($formatif_4 ? "'$formatif_4'" : "NULL") . ", 
                          " . ($sts ? "'$sts'" : "NULL") . ", 
                          " . ($sas ? "'$sas'" : "NULL") . ", 
                          '$nilai_akhir', '$predikat', $id_user)";
            }
            
            if (mysqli_query($conn, $query)) {
                $success_count++;
            }
        }
    }
    
    if ($success_count > 0) {
        $success = "Berhasil menyimpan $success_count nilai!";
        // Refresh data
        header("Location: index.php?rombel=$filter_rombel&mapel=$filter_mapel");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai - Aplikasi Raport SMK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .saving-indicator {
            display: inline-block;
            margin-left: 5px;
            font-size: 12px;
            color: #ff9800;
        }
        .save-success {
            color: #4caf50 !important;
        }
        .save-error {
            color: #f44336 !important;
        }
        .input-wrapper {
            position: relative;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <h2>Input Nilai Siswa</h2>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="../../logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
            
            <?php if (isset($error_semester)): ?>
                <div class="alert alert-danger"><?php echo $error_semester; ?></div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h3>Pilih Kelas dan Mata Pelajaran</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Semester Aktif:</strong> <?php echo $semester_aktif['nama_semester']; ?></p>
                        <br>
                        <form method="GET" action="" class="form-inline">
                            <div class="form-group">
                                <label for="rombel">Rombel *</label>
                                <select id="rombel" name="rombel" class="form-control" onchange="loadMapelByRombel()" required>
                                    <option value="">-- Pilih Rombel --</option>
                                    <?php 
                                    mysqli_data_seek($result_mapel_guru, 0);
                                    $rombel_added = [];
                                    while ($mg = mysqli_fetch_assoc($result_mapel_guru)): 
                                        if (!in_array($mg['id_rombel'], $rombel_added)):
                                            $rombel_added[] = $mg['id_rombel'];
                                    ?>
                                        <option value="<?php echo $mg['id_rombel']; ?>" 
                                            <?php echo $filter_rombel == $mg['id_rombel'] ? 'selected' : ''; ?>>
                                            <?php echo $mg['nama_rombel']; ?>
                                        </option>
                                    <?php 
                                        endif;
                                    endwhile; 
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="mapel">Mata Pelajaran *</label>
                                <select id="mapel" name="mapel" class="form-control" required>
                                    <option value="">-- Pilih Mapel --</option>
                                    <?php 
                                    mysqli_data_seek($result_mapel_guru, 0);
                                    $mapel_added = [];
                                    while ($mg = mysqli_fetch_assoc($result_mapel_guru)): 
                                        if (!in_array($mg['id_mapel'], $mapel_added)):
                                            $mapel_added[] = $mg['id_mapel'];
                                    ?>
                                        <option value="<?php echo $mg['id_mapel']; ?>" 
                                            data-rombel="<?php echo $mg['id_rombel']; ?>"
                                            <?php echo $filter_mapel == $mg['id_mapel'] ? 'selected' : ''; ?>>
                                            <?php echo $mg['kode_mapel']; ?> - <?php echo $mg['nama_mapel']; ?>
                                        </option>
                                    <?php 
                                        endif;
                                    endwhile; 
                                    ?>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Tampilkan</button>
                        </form>
                    </div>
                </div>
                
                <?php if (!empty($siswa_list)): ?>
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="card">
                            <div class="card-header">
                                <h3>Daftar Siswa dan Nilai</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th rowspan="2">No</th>
                                                <th rowspan="2">Nama Siswa</th>
                                                <th colspan="4" style="text-align:center;">Formatif</th>
                                                <th rowspan="2">STS</th>
                                                <th rowspan="2">SAS</th>
                                                <th rowspan="2">Rata-rata</th>
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
                                            // Ambil KKM mata pelajaran
                                            $query_kkm = "SELECT kkm FROM mata_pelajaran WHERE id_mapel = '$filter_mapel'";
                                            $result_kkm = mysqli_query($conn, $query_kkm);
                                            $kkm = $result_kkm ? mysqli_fetch_assoc($result_kkm)['kkm'] : 75;
                                            
                                            foreach ($siswa_list as $siswa): 
                                                $nilai = $siswa['nilai'];
                                                
                                                // Hitung rata-rata jika ada nilai
                                                $rata_rata = '';
                                                $warna_rata = '#333';
                                                if ($nilai) {
                                                    $nilai_array = array_filter([
                                                        $nilai['nilai_formatif_1'],
                                                        $nilai['nilai_formatif_2'],
                                                        $nilai['nilai_formatif_3'],
                                                        $nilai['nilai_formatif_4'],
                                                        $nilai['nilai_sts'],
                                                        $nilai['nilai_sas']
                                                    ], function($v) { return $v !== null && $v !== ''; });
                                                    
                                                    if (count($nilai_array) > 0) {
                                                        $rata_rata = number_format(array_sum($nilai_array) / count($nilai_array), 2);
                                                        if ($rata_rata < $kkm) {
                                                            $warna_rata = 'red';
                                                        } else {
                                                            $warna_rata = 'green';
                                                        }
                                                    }
                                                }
                                            ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td>
                                                    <strong><?php echo $siswa['nama_lengkap']; ?></strong><br>
                                                    <small style="color: #666;">NIS: <?php echo $siswa['nis']; ?></small>
                                                </td>
                                                <td>
                                                    <div class="input-wrapper">
                                                        <input type="number" 
                                                               name="siswa[<?php echo $siswa['id_siswa']; ?>][formatif_1]" 
                                                               class="form-control nilai-input" 
                                                               data-siswa="<?php echo $siswa['id_siswa']; ?>"
                                                               data-field="formatif_1"
                                                               value="<?php echo $nilai ? $nilai['nilai_formatif_1'] : ''; ?>" 
                                                               min="0" max="100" step="0.01" style="width: 80px;">
                                                        <span class="saving-indicator" id="status-<?php echo $siswa['id_siswa']; ?>-formatif_1"></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-wrapper">
                                                        <input type="number" 
                                                               name="siswa[<?php echo $siswa['id_siswa']; ?>][formatif_2]" 
                                                               class="form-control nilai-input" 
                                                               data-siswa="<?php echo $siswa['id_siswa']; ?>"
                                                               data-field="formatif_2"
                                                               value="<?php echo $nilai ? $nilai['nilai_formatif_2'] : ''; ?>" 
                                                               min="0" max="100" step="0.01" style="width: 80px;">
                                                        <span class="saving-indicator" id="status-<?php echo $siswa['id_siswa']; ?>-formatif_2"></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-wrapper">
                                                        <input type="number" 
                                                               name="siswa[<?php echo $siswa['id_siswa']; ?>][formatif_3]" 
                                                               class="form-control nilai-input" 
                                                               data-siswa="<?php echo $siswa['id_siswa']; ?>"
                                                               data-field="formatif_3"
                                                               value="<?php echo $nilai ? $nilai['nilai_formatif_3'] : ''; ?>" 
                                                               min="0" max="100" step="0.01" style="width: 80px;">
                                                        <span class="saving-indicator" id="status-<?php echo $siswa['id_siswa']; ?>-formatif_3"></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-wrapper">
                                                        <input type="number" 
                                                               name="siswa[<?php echo $siswa['id_siswa']; ?>][formatif_4]" 
                                                               class="form-control nilai-input" 
                                                               data-siswa="<?php echo $siswa['id_siswa']; ?>"
                                                               data-field="formatif_4"
                                                               value="<?php echo $nilai ? $nilai['nilai_formatif_4'] : ''; ?>" 
                                                               min="0" max="100" step="0.01" style="width: 80px;">
                                                        <span class="saving-indicator" id="status-<?php echo $siswa['id_siswa']; ?>-formatif_4"></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-wrapper">
                                                        <input type="number" 
                                                               name="siswa[<?php echo $siswa['id_siswa']; ?>][sts]" 
                                                               class="form-control nilai-input" 
                                                               data-siswa="<?php echo $siswa['id_siswa']; ?>"
                                                               data-field="sts"
                                                               value="<?php echo $nilai ? $nilai['nilai_sts'] : ''; ?>" 
                                                               min="0" max="100" step="0.01" style="width: 80px;">
                                                        <span class="saving-indicator" id="status-<?php echo $siswa['id_siswa']; ?>-sts"></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-wrapper">
                                                        <input type="number" 
                                                               name="siswa[<?php echo $siswa['id_siswa']; ?>][sas]" 
                                                               class="form-control nilai-input" 
                                                               data-siswa="<?php echo $siswa['id_siswa']; ?>"
                                                               data-field="sas"
                                                               value="<?php echo $nilai ? $nilai['nilai_sas'] : ''; ?>" 
                                                               min="0" max="100" step="0.01" style="width: 80px;">
                                                        <span class="saving-indicator" id="status-<?php echo $siswa['id_siswa']; ?>-sas"></span>
                                                    </div>
                                                </td>
                                                <td style="text-align:center;font-weight:bold;color:<?php echo $warna_rata; ?>;" id="rata-<?php echo $siswa['id_siswa']; ?>">
                                                    <?php echo $rata_rata; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-20">
                                    <p><strong>KKM Mata Pelajaran:</strong> <?php echo $kkm; ?></p>
                                    <p><em>Nilai Raport = Rata-rata dari 6 parameter (Formatif 1-4, STS, SAS)</em></p>
                                    <p><em>Perubahan nilai akan tersimpan otomatis</em></p>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Variabel untuk menyimpan ID semester aktif dan mata pelajaran
        const idSemester = <?php echo $semester_aktif ? $semester_aktif['id_semester'] : 0; ?>;
        const idMapel = '<?php echo $filter_mapel; ?>';
        const kkm = <?php echo isset($kkm) ? $kkm : 75; ?>;
        
        // Fungsi untuk load mata pelajaran berdasarkan rombel
        function loadMapelByRombel() {
            const rombelSelect = document.getElementById('rombel');
            const mapelSelect = document.getElementById('mapel');
            const idRombel = rombelSelect.value;
            
            // Reset dropdown mapel
            mapelSelect.innerHTML = '<option value="">-- Loading... --</option>';
            mapelSelect.disabled = true;
            
            if (!idRombel) {
                mapelSelect.innerHTML = '<option value="">-- Pilih Rombel Dulu --</option>';
                return;
            }
            
            // Fetch mata pelajaran dari server
            fetch('get_mapel_by_rombel.php?id_rombel=' + idRombel)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        mapelSelect.innerHTML = '<option value="">-- Pilih Mata Pelajaran --</option>';
                        
                        data.data.forEach(mapel => {
                            const option = document.createElement('option');
                            option.value = mapel.id_mapel;
                            option.textContent = mapel.kode_mapel + ' - ' + mapel.nama_mapel;
                            
                            // Pertahankan pilihan jika sudah ada
                            if (mapel.id_mapel == idMapel) {
                                option.selected = true;
                            }
                            
                            mapelSelect.appendChild(option);
                        });
                        
                        mapelSelect.disabled = false;
                    } else {
                        mapelSelect.innerHTML = '<option value="">-- Tidak Ada Mata Pelajaran --</option>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mapelSelect.innerHTML = '<option value="">-- Error Loading Data --</option>';
                });
        }
        
        // Timeout untuk debouncing
        let saveTimeouts = {};
        
        // Fungsi untuk menyimpan nilai via AJAX
        function saveNilai(idSiswa, field, value, statusElement, rataElement) {
            // Clear timeout sebelumnya jika ada
            const timeoutKey = idSiswa + '-' + field;
            if (saveTimeouts[timeoutKey]) {
                clearTimeout(saveTimeouts[timeoutKey]);
            }
            
            // Set timeout baru (delay 500ms untuk debouncing)
            saveTimeouts[timeoutKey] = setTimeout(() => {
                // Tampilkan indikator saving
                statusElement.textContent = '💾 Menyimpan...';
                statusElement.className = 'saving-indicator';
                
                // Buat FormData
                const formData = new FormData();
                formData.append('id_siswa', idSiswa);
                formData.append('id_mapel', idMapel);
                formData.append('id_semester', idSemester);
                formData.append('field', field);
                formData.append('value', value);
                
                // Kirim request AJAX
                fetch('save_nilai_ajax.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        statusElement.textContent = '✓ Tersimpan';
                        statusElement.className = 'saving-indicator save-success';
                        
                        // Update rata-rata jika ada
                        if (rataElement && data.rata_rata !== undefined) {
                            rataElement.textContent = data.rata_rata;
                            
                            // Update warna berdasarkan KKM
                            if (data.rata_rata !== '') {
                                const nilaiRata = parseFloat(data.rata_rata);
                                rataElement.style.color = nilaiRata < kkm ? 'red' : 'green';
                            } else {
                                rataElement.style.color = '#333';
                            }
                        }
                        
                        // Hilangkan status setelah 2 detik
                        setTimeout(() => {
                            statusElement.textContent = '';
                        }, 2000);
                    } else {
                        statusElement.textContent = '✗ Gagal';
                        statusElement.className = 'saving-indicator save-error';
                        console.error('Error:', data.message);
                        
                        // Hilangkan status setelah 3 detik
                        setTimeout(() => {
                            statusElement.textContent = '';
                        }, 3000);
                    }
                })
                .catch(error => {
                    statusElement.textContent = '✗ Error';
                    statusElement.className = 'saving-indicator save-error';
                    console.error('Error:', error);
                    
                    // Hilangkan status setelah 3 detik
                    setTimeout(() => {
                        statusElement.textContent = '';
                    }, 3000);
                });
            }, 500);
        }
        
        // Attach event listener ke semua input nilai
        document.addEventListener('DOMContentLoaded', function() {
            const nilaiInputs = document.querySelectorAll('.nilai-input');
            
            nilaiInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const idSiswa = this.getAttribute('data-siswa');
                    const field = this.getAttribute('data-field');
                    const value = this.value;
                    const statusElement = document.getElementById('status-' + idSiswa + '-' + field);
                    const rataElement = document.getElementById('rata-' + idSiswa);
                    
                    // Validasi nilai untuk input number
                    if (this.type === 'number' && value !== '') {
                        const numValue = parseFloat(value);
                        if (numValue < 0 || numValue > 100) {
                            alert('Nilai harus antara 0-100');
                            this.value = '';
                            return;
                        }
                    }
                    
                    saveNilai(idSiswa, field, value, statusElement, rataElement);
                });
                
                // Tambahan: auto-save saat user blur dari input (keluar dari field)
                input.addEventListener('blur', function() {
                    const idSiswa = this.getAttribute('data-siswa');
                    const field = this.getAttribute('data-field');
                    const value = this.value;
                    const statusElement = document.getElementById('status-' + idSiswa + '-' + field);
                    const rataElement = document.getElementById('rata-' + idSiswa);
                    
                    // Hanya save jika ada perubahan
                    if (this.defaultValue !== value) {
                        saveNilai(idSiswa, field, value, statusElement, rataElement);
                        this.defaultValue = value;
                    }
                });
            });
        });
    </script>
</body>
</html>
