<div class="sidebar" id="sidebar">
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        <span id="toggleIcon">◀</span>
    </button>
    <div class="sidebar-header">
        <h3>Raport SMK</h3>
        <p>Administrator Panel</p>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">📊 Dashboard</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/users/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/users/') !== false ? 'active' : ''; ?>">👥 Manajemen User</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/jurusan/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/jurusan/') !== false ? 'active' : ''; ?>">🎓 Jurusan</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/rombel/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/rombel/') !== false ? 'active' : ''; ?>">🏫 Rombongan Belajar</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/mapel/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/mapel/') !== false && strpos($_SERVER['PHP_SELF'], '/guru_mapel/') === false ? 'active' : ''; ?>">📚 Mata Pelajaran</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/guru_mapel/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/guru_mapel/') !== false ? 'active' : ''; ?>">👨‍🏫 Jadwal Guru</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/siswa/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/siswa/') !== false ? 'active' : ''; ?>">👨‍🎓 Data Siswa</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/semester/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/semester/') !== false ? 'active' : ''; ?>">📅 Semester & TA</a></li>
        <li><a href="<?php echo BASE_URL; ?>admin/nilai/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/nilai/') !== false ? 'active' : ''; ?>">📝 Nilai Siswa</a></li>
    </ul>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const icon = document.getElementById('toggleIcon');
    
    sidebar.classList.toggle('collapsed');
    
    if (sidebar.classList.contains('collapsed')) {
        icon.innerHTML = '▶';
        if (mainContent) {
            mainContent.style.marginLeft = '60px';
        }
    } else {
        icon.innerHTML = '◀';
        if (mainContent) {
            mainContent.style.marginLeft = '250px';
        }
    }
}
</script>
