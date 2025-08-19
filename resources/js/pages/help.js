function initHelpPage() {
    const navbar = document.getElementById('navbar-help');

    // Scrollspy-like behavior
    const sections = Array.from(document.querySelectorAll('section'));
    const navLinks = navbar ? Array.from(navbar.querySelectorAll('.nav-link')) : [];

    function getOffset(fromNavLinks = false) {
        return window.innerWidth <= 768 && !fromNavLinks ? 200 : 200;
    }

    function updateActiveLink() {
        const fromTop = window.scrollY + getOffset();
        const currentSection = sections.find((section) => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            return fromTop >= sectionTop && fromTop < sectionTop + sectionHeight;
        });
        if (navbar && currentSection) {
            const newActiveLink = navbar.querySelector(`a[href="#${currentSection.id}"]`);
            if (newActiveLink && !newActiveLink.classList.contains('active')) {
                navLinks.forEach((link) => link.classList.remove('active'));
                newActiveLink.classList.add('active');
            }
        }
    }

    updateActiveLink();
    window.addEventListener('scroll', updateActiveLink);

    navLinks.forEach((link) => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            const offset = targetId === 'contactUs' ? getOffset(false) : getOffset(true);
            const targetPosition = targetSection.offsetTop - offset + 125;
            window.scrollTo({ top: targetPosition, behavior: 'smooth' });
        });
    });

    // Teacher read-more logic
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
                if (teacherName) {
                    const offset = 60;
                    const elementPosition = teacherName.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - offset;
                    window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
                }
            } else if (shortBio && fullBio) {
                shortBio.classList.remove('d-none');
                fullBio.classList.add('d-none');
                this.textContent = 'Read More';
                const teacherElement = this.closest('.card')?.querySelector('.teacher-image-container');
                if (teacherElement) {
                    const offset = 70;
                    const elementPosition = teacherElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - offset;
                    window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
                }
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHelpPage);
} else {
    initHelpPage();
}


