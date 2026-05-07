    </main>
</div>

<script>
    // Toggle sidebar di mobile
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        
        sidebar.classList.toggle('hidden');
        sidebar.classList.toggle('fixed');
        sidebar.classList.toggle('inset-y-0');
        sidebar.classList.toggle('left-0');
        sidebar.classList.toggle('z-50');
        overlay.classList.toggle('hidden');
    }

    // Toggle submenu laporan
    function toggleSubmenu(id) {
        const submenu = document.getElementById(id);
        const icon = document.getElementById('icon-laporan');
        
        submenu.classList.toggle('hidden');
        
        // Rotate icon
        if (submenu.classList.contains('hidden')) {
            icon.style.transform = 'rotate(0deg)';
        } else {
            icon.style.transform = 'rotate(180deg)';
        }
    }

    // Buka submenu laporan jika di halaman laporan
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = '<?= $current_page ?>';
        if (currentPage === 'laporan') {
            document.getElementById('submenu-laporan').classList.remove('hidden');
            document.getElementById('icon-laporan').style.transform = 'rotate(180deg)';
        }
    });

    // Jam realtime
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