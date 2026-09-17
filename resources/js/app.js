function initMobileMenu() {
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.getElementById('mobile-menu');

    if (!toggle || !menu) {
        return;
    }

    const setOpen = (open) => {
        menu.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
        toggle.querySelector('[data-menu-label]').textContent = open ? toggle.dataset.closeLabel || 'Close menu' : toggle.dataset.openLabel || 'Open menu';
        toggle.querySelector('[data-menu-open-icon]').classList.toggle('hidden', open);
        toggle.querySelector('[data-menu-close-icon]').classList.toggle('hidden', !open);
    };

    toggle.addEventListener('click', () => setOpen(menu.hidden));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !menu.hidden) {
            setOpen(false);
            toggle.focus();
        }
    });

    window.matchMedia('(min-width: 1280px)').addEventListener('change', (event) => {
        if (event.matches) {
            setOpen(false);
        }
    });
}

/** Close <details data-dropdown> menus when clicking outside them or pressing Escape. */
function initDropdowns() {
    const openDropdowns = () => document.querySelectorAll('details[data-dropdown][open]');

    document.addEventListener('click', (event) => {
        openDropdowns().forEach((dropdown) => {
            if (!dropdown.contains(event.target)) {
                dropdown.removeAttribute('open');
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        openDropdowns().forEach((dropdown) => {
            dropdown.removeAttribute('open');
            dropdown.querySelector('summary')?.focus();
        });
    });
}

initMobileMenu();
initDropdowns();
