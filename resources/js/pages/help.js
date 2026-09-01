function initHelpPage() {
    const navbar = document.getElementById('navbar-help');
    const sections = Array.from(document.querySelectorAll('.about-page section[id]'));
    const navLinks = navbar
        ? Array.from(navbar.querySelectorAll('a.nav-link[href^="#"]'))
        : [];
    const tabsWrap = navbar?.querySelector('.about-tabs-wrap');

    function scrollToTarget(el) {
        if (!el) return;
        el.scrollIntoView({ block: 'start', behavior: 'auto' });
    }

    function updateActiveLink() {
        const offset = (navbar?.offsetHeight || 0) + 16;
        const fromTop = window.scrollY + offset + 8;
        const isAtBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 10;

        let currentSection;
        if (isAtBottom) {
            currentSection = sections[sections.length - 1];
        } else {
            currentSection = sections.find((section) => {
                const top = section.getBoundingClientRect().top + window.scrollY;
                const bottom = top + section.offsetHeight;
                return fromTop >= top && fromTop < bottom;
            });
        }

        if (navbar && currentSection) {
            const newActiveLink = navbar.querySelector(`a.nav-link[href="#${currentSection.id}"]`);
            if (newActiveLink && !newActiveLink.classList.contains('active')) {
                navLinks.forEach((link) => link.classList.remove('active'));
                newActiveLink.classList.add('active');
                if (tabsWrap) {
                    const left = newActiveLink.offsetLeft - tabsWrap.clientWidth / 2 + newActiveLink.offsetWidth / 2;
                    tabsWrap.scrollTo({ left: Math.max(0, left), behavior: 'smooth' });
                }
            }
        }
    }

    updateActiveLink();
    window.addEventListener('scroll', updateActiveLink, { passive: true });

    navLinks.forEach((link) => {
        link.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (!target) return;
            e.preventDefault();
            scrollToTarget(target);
            history.replaceState(null, '', this.getAttribute('href'));
        });
    });

    document.querySelectorAll('.about-resource a[href^="#"]').forEach((link) => {
        link.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (!target) return;
            e.preventDefault();
            scrollToTarget(target);
        });
    });

    document.querySelectorAll('.read-more').forEach((button) => {
        button.addEventListener('click', function () {
            const cardBody = this.closest('.card-body');
            const shortBio = cardBody?.querySelector('.short-bio');
            const fullBio = cardBody?.querySelector('.full-bio');
            const teacherIndex = this.getAttribute('data-teacher-index');

            if (shortBio && fullBio && !shortBio.classList.contains('d-none')) {
                shortBio.classList.add('d-none');
                fullBio.classList.remove('d-none');
                this.textContent = 'Read Less';
                const teacherName = document.querySelector(`#teacher-name-${teacherIndex}`);
                if (teacherName) scrollToTarget(teacherName);
            } else if (shortBio && fullBio) {
                shortBio.classList.remove('d-none');
                fullBio.classList.add('d-none');
                this.textContent = 'Read More';
                const teacherElement = this.closest('.teacher-row')?.querySelector('.teacher-image-container');
                if (teacherElement) scrollToTarget(teacherElement);
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHelpPage);
} else {
    initHelpPage();
}
