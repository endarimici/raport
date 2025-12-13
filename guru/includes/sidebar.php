<div class="sidebar" id="sidebar">
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        <span id="toggleIcon">◀</span>
    </button>
    <div class="sidebar-header">
        <h3>Raport SMK</h3>
        <p>Guru Panel</p>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="<?php echo BASE_URL; ?>guru/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">📊 Dashboard</a></li>
        <li><a href="<?php echo BASE_URL; ?>guru/nilai/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/nilai/') !== false ? 'active' : ''; ?>">📝 Input Nilai</a></li>
        <li><a href="<?php echo BASE_URL; ?>guru/nilai/download.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'download.php' ? 'active' : ''; ?>">💾 Download Raport</a></li>
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
