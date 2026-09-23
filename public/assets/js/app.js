(function () {
    'use strict';

    var menuToggle = document.querySelector('[data-menu-toggle]');
    var mobileNav = document.querySelector('[data-mobile-nav]');

    function setMenu(open) {
        menuToggle.setAttribute('aria-expanded', String(open));
        menuToggle.querySelector('.visually-hidden').textContent = open ? 'Tutup menu' : 'Buka menu';
        mobileNav.hidden = !open;
        document.body.style.overflow = open ? 'hidden' : '';
    }

    if (menuToggle && mobileNav) {
        menuToggle.addEventListener('click', function () {
            setMenu(menuToggle.getAttribute('aria-expanded') !== 'true');
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && menuToggle.getAttribute('aria-expanded') === 'true') {
                setMenu(false);
                menuToggle.focus();
            }
        });

        // Resizing past the desktop breakpoint leaves the panel hidden but the
        // body still locked, so reset state when the panel stops being reachable.
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1100 && menuToggle.getAttribute('aria-expanded') === 'true') {
                setMenu(false);
            }
        });
    }

    document.querySelectorAll('[data-submenu-toggle]').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var submenu = document.getElementById(toggle.getAttribute('aria-controls'));
            var open = toggle.getAttribute('aria-expanded') !== 'true';

            toggle.setAttribute('aria-expanded', String(open));
            submenu.hidden = !open;
        });
    });
}());
