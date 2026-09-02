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

    document.querySelectorAll('[data-teacher-bio]').forEach((bio) => {
        const shortBio = bio.querySelector('[data-teacher-bio-short]');
        const fullBio = bio.querySelector('[data-teacher-bio-full]');
        const toggle = bio.querySelector('[data-teacher-bio-toggle]');
        if (!shortBio || !fullBio || !toggle) return;

        toggle.addEventListener('click', () => {
            const expanded = bio.classList.toggle('is-expanded');
            shortBio.classList.toggle('d-none', expanded);
            fullBio.classList.toggle('d-none', !expanded);
            toggle.textContent = expanded ? 'Read less' : 'Read more';
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHelpPage);
} else {
    initHelpPage();
}
