(function () {
    'use strict';

    // Destructive admin forms (delete, suspend) ask first. Without JS they
    // still work; the server-side guards (e.g. pit stops with bookings) apply.
    document.addEventListener('submit', function (event) {
        var form = event.target.closest('[data-confirm]');

        if (form && !window.confirm(form.getAttribute('data-confirm'))) {
            event.preventDefault();
        }
    });
}());
