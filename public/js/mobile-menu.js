// Mobile Menu Toggle
(function() {
    const sidebar = document.getElementById('sidebar');
    const hamburger = document.getElementById('hamburgerMenu');
    const overlay = document.getElementById('mobileOverlay');
    
    if (!hamburger || !sidebar || !overlay) return;
    
    // Toggle sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
    }
    
    // Close sidebar
    function closeSidebar() {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Event listeners
    hamburger.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', closeSidebar);
    
    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });
    
    // Close sidebar when clicking nav links on mobile
    const navLinks = sidebar.querySelectorAll('.sidebar-nav-item');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                setTimeout(closeSidebar, 300);
            }
        });
    });
})();