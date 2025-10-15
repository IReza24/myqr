</main>
    </div>
</div>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<!-- Tambahkan SweetAlert2 untuk dialog konfirmasi yang lebih baik -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
<?php
if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    // Gunakan json_encode untuk menangani karakter khusus dengan aman
    $message = json_encode($toast['message']); 
    $type = $toast['type'];
    $borderLeftColor = ($type === 'success') ? '#28a745' : '#dc3545';

    echo "
    document.addEventListener('DOMContentLoaded', function() {
        Toastify({
            text: $message, duration: 3000, close: true, gravity: 'bottom', position: 'right', stopOnFocus: true,
            style: { background: '#ffffff', color: '#333333', borderRadius: '8px', borderLeft: '5px solid $borderLeftColor', boxShadow: '0 3px 6px -1px rgba(0, 0, 0, 0.12), 0 10px 36px -4px rgba(77, 96, 232, 0.15)' },
            offset: { x: 20, y: 20 }
        }).showToast();
    });
    ";
    unset($_SESSION['toast']); // Hapus pesan setelah ditampilkan
}
?>

// --- JAVASCRIPT BARU UNTUK STRUKTUR BARU ---
const sidebar = document.getElementById('sidebar');
const contentWrapper = document.querySelector('.content-wrapper');
const sidebarToggleDesktop = document.getElementById('sidebar-toggle-desktop');
const mobileToggleButtons = document.querySelectorAll('#sidebar-toggle-mobile');
const mobileCloseButton = document.getElementById('sidebar-close-mobile');
const sidebarOverlay = document.getElementById('sidebar-overlay');

function setSidebarState(isCollapsed) {
    if (sidebar) {
        sidebar.classList.toggle('is-collapsed', isCollapsed);
    }
    // Sekarang kita mengubah margin dari content-wrapper
    if (contentWrapper) {
        contentWrapper.classList.toggle('md:ml-20', isCollapsed);
        contentWrapper.classList.toggle('md:ml-64', !isCollapsed);
    }
}

// --- LOGIKA DESKTOP ---
if (sidebar && contentWrapper && sidebarToggleDesktop) {
    sidebarToggleDesktop.addEventListener('click', () => {
        const isCollapsed = !sidebar.classList.contains('is-collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
        setSidebarState(isCollapsed);
    });

    sidebar.addEventListener('mouseenter', () => {
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            setSidebarState(false);
        }
    });

    sidebar.addEventListener('mouseleave', () => {
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            setSidebarState(true);
        }
    });
}

// --- LOGIKA MOBILE ---
function openMobileSidebar() { sidebar.classList.remove('-translate-x-full'); sidebarOverlay.classList.remove('hidden'); }
function closeMobileSidebar() { sidebar.classList.add('-translate-x-full'); sidebarOverlay.classList.add('hidden'); }

if (mobileToggleButtons.length > 0) mobileToggleButtons.forEach(btn => btn.addEventListener('click', openMobileSidebar));
if (mobileCloseButton) mobileCloseButton.addEventListener('click', closeMobileSidebar);
if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeMobileSidebar);


document.addEventListener('DOMContentLoaded', () => {
    if (window.innerWidth >= 768 && sidebar) {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        setSidebarState(isCollapsed);
    }
});
</script>
</body>
</html>