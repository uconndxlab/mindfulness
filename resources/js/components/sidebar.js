document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('admin-sidebar');
    const openBtn = document.getElementById('sidebar-open');
    const closeBtn = document.getElementById('sidebar-close');

    if (sidebar && openBtn && closeBtn) {
        openBtn.addEventListener('click', () => {
            sidebar.classList.add('show');
        });

        closeBtn.addEventListener('click', () => {
            sidebar.classList.remove('show');
        });

        document.addEventListener('click', (event) => {
            if (window.innerWidth < 768) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnOpenBtn = openBtn.contains(event.target);

            if (!isClickInsideSidebar && !isClickOnOpenBtn && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            }
        });
    }
});