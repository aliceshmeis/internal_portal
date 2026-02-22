document.addEventListener('DOMContentLoaded', function () {
    const sidebar   = document.getElementById('sidebar');
    const hamburger = document.getElementById('hamburgerMenu');
    const overlay   = document.getElementById('mobileOverlay');
    const main      = document.querySelector('.main-content');
    const topbar    = document.querySelector('.topbar');

    if (!sidebar || !hamburger) return;

    const isMobile = () => window.innerWidth <= 1024;

    hamburger.addEventListener('click', function () {
        if (isMobile()) {
            sidebar.classList.toggle('mobile-open');
            if (overlay) overlay.classList.toggle('active');
        } else {
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            const w = isCollapsed ? '60px' : '240px'; // fixed: was 230px
            if (topbar) topbar.style.left = w;
            if (main)   main.style.marginLeft = w;
        }
    });

    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        });
    }

    window.addEventListener('resize', function () {
        if (!isMobile()) {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
            const isCollapsed = sidebar.classList.contains('collapsed');
            const w = isCollapsed ? '60px' : '240px'; // fixed: was 230px
            if (topbar) topbar.style.left = w;
            if (main)   main.style.marginLeft = w;
        } else {
            sidebar.classList.remove('collapsed');
            if (topbar) topbar.style.left = '';
            if (main)   main.style.marginLeft = '';
        }
    });
});