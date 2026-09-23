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

        // An in-page link (e.g. /#daftar while on the home page) doesn't load a
        // new page, so the panel has to be closed by hand.
        mobileNav.addEventListener('click', function (event) {
            if (event.target.closest('a')) {
                setMenu(false);
            }
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

    // Home hero: cross-fade background photos. Skipped for a single slide and
    // for visitors who ask for reduced motion; paused while the tab is hidden.
    var slideshow = document.querySelector('[data-hero-slides]');
    var slides = slideshow ? slideshow.querySelectorAll('.hero__slide') : [];
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (slides.length > 1 && !reduceMotion) {
        var current = 0;

        setInterval(function () {
            if (document.hidden) {
                return;
            }

            slides[current].classList.remove('is-active');
            current = (current + 1) % slides.length;

            // Later slides are lazy; start fetching the image before it fades in.
            var img = slides[current].querySelector('img');
            if (img) {
                img.loading = 'eager';
            }

            slides[current].classList.add('is-active');
        }, 6000);
    }

    // Hearts: post in the background and update in place. Without JS (or if
    // this fails) the form simply submits and the server redirects back.
    document.addEventListener('submit', function (event) {
        var form = event.target.closest('[data-like-form]');

        if (!form || !window.fetch) {
            return;
        }

        event.preventDefault();
        var button = form.querySelector('button');
        button.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { Accept: 'application/json' },
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.ok ? response.json() : Promise.reject(response);
            })
            .then(function (data) {
                button.setAttribute('aria-pressed', String(data.liked));
                form.querySelector('[data-like-count]').textContent = data.label;
                form.querySelector('[data-like-total]').textContent = data.count.toLocaleString('en-US');
            })
            .catch(function () {
                form.submit();
            })
            .finally(function () {
                button.disabled = false;
            });
    });

    // "View Location" opens the map in a new tab; tell the server so the eye
    // count goes up. sendBeacon survives the page losing focus.
    document.addEventListener('click', function (event) {
        var link = event.target.closest('[data-view-beacon]');
        var token = document.querySelector('input[name="_csrf_token"]');

        if (!link || !token || !navigator.sendBeacon) {
            return;
        }

        var body = new FormData();
        body.append('_csrf_token', token.value);
        navigator.sendBeacon(link.getAttribute('data-view-beacon'), body);
    });

    document.querySelectorAll('[data-submenu-toggle]').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var submenu = document.getElementById(toggle.getAttribute('aria-controls'));
            var open = toggle.getAttribute('aria-expanded') !== 'true';

            toggle.setAttribute('aria-expanded', String(open));
            submenu.hidden = !open;
        });
    });
}());
