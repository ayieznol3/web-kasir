    </main>
</div>

<!-- Tombol munculkan sidebar (desktop) -->
<button id="btn-show-sidebar" 
        onclick="toggleSidebar()" 
        class="hidden fixed top-20 left-2 z-50 bg-primary text-white w-8 h-8 rounded-full shadow-lg hover:bg-indigo-700 transition"
        title="Tampilkan Menu">
    <i class="fas fa-chevron-right text-xs"></i>
</button>

<script>
// ==================== TOGGLE SIDEBAR ====================
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const showBtn = document.getElementById('btn-show-sidebar');
    
    if (sidebar.style.display === 'none' || sidebar.classList.contains('hidden')) {
        sidebar.style.display = 'block';
        sidebar.classList.remove('hidden');
        if (showBtn) showBtn.classList.add('hidden');
        localStorage.setItem('sidebar-hidden', 'false');
    } else {
        sidebar.style.display = 'none';
        sidebar.classList.add('hidden');
        if (showBtn) showBtn.classList.remove('hidden');
        localStorage.setItem('sidebar-hidden', 'true');
    }
    
    if (overlay && window.innerWidth < 1024) {
        overlay.classList.toggle('hidden');
    }
}

// ==================== TOGGLE SUBMENU ====================
function toggleSubmenu(id) {
    const submenu = document.getElementById(id);
    submenu.classList.toggle('hidden');
    
    // Update ikon
    const iconId = 'icon-' + id.replace('submenu-', '');
    const icon = document.getElementById(iconId);
    if (icon) {
        icon.classList.toggle('open', !submenu.classList.contains('hidden'));
    }
}

// ==================== CEK STATE SAAT LOAD ====================
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const showBtn = document.getElementById('btn-show-sidebar');
    const isHidden = localStorage.getItem('sidebar-hidden');
    
    // Default: tampilkan sidebar di desktop, sembunyikan di mobile
    if (isHidden === null) {
        // Pertama kali buka
        if (window.innerWidth >= 1024) {
            // Desktop: tampilkan
            sidebar.style.display = 'block';
            sidebar.classList.remove('hidden');
            localStorage.setItem('sidebar-hidden', 'false');
        } else {
            // Mobile: sembunyikan
            sidebar.style.display = 'none';
            sidebar.classList.add('hidden');
            localStorage.setItem('sidebar-hidden', 'true');
        }
    } else if (isHidden === 'true') {
        sidebar.style.display = 'none';
        sidebar.classList.add('hidden');
        if (showBtn && window.innerWidth >= 1024) showBtn.classList.remove('hidden');
    } else {
        sidebar.style.display = 'block';
        sidebar.classList.remove('hidden');
        if (showBtn) showBtn.classList.add('hidden');
    }
});
// ==================== JAM REALTIME ====================
function updateJam() {
    const now = new Date();
    const jam = document.getElementById('jam');
    if (jam) {
        jam.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }
}
setInterval(updateJam, 1000);
updateJam();
</script>

</body>
</html>