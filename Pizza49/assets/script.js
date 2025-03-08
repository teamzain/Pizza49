    // JavaScript remains the same as in the previous version
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.sidebar');
        const collapseBtn = document.querySelector('.collapse-btn');
        const logoText = document.querySelector('.logo-text');
        const menuItems = document.querySelectorAll('.menu-item span');
        const mainContent = document.querySelector('.main-content');
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');

        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('active');
            mainContent.style.marginLeft = sidebar.classList.contains('collapsed') ? '80px' : '250px';

            if (sidebar.classList.contains('collapsed')) {
                logoText.style.display = 'none';
                menuItems.forEach(item => item.style.display = 'none');
                collapseBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
            } else {
                logoText.style.display = 'block';
                menuItems.forEach(item => item.style.display = 'inline');
                collapseBtn.innerHTML = '<i class="fas fa-chevron-left"></i><span>Collapse</span>';
            }
        }

        collapseBtn.addEventListener('click', toggleSidebar);
        mobileMenuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        const menuLinks = document.querySelectorAll('.menu-item');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                menuLinks.forEach(item => item.classList.remove('active'));
                this.classList.add('active');
            });
        });

        document.addEventListener('click', function(event) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnMobileToggle = mobileMenuToggle.contains(event.target);
            if (!isClickInsideSidebar && !isClickOnMobileToggle && window.innerWidth <= 768) {
                sidebar.classList.remove('active');
            }
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                mainContent.style.marginLeft = sidebar.classList.contains('collapsed') ? '80px' : '250px';
            } else {
                mainContent.style.marginLeft = '0';
            }
        });
    });