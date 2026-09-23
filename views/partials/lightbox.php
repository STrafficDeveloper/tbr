<?php

declare(strict_types=1);

/**
 * One <dialog> per page for photos and videos: the design's "pop up" views.
 * <dialog> gives focus trapping, Escape-to-close and a backdrop natively.
 */
?>
<dialog class="lightbox" data-lightbox aria-label="Paparan media">
    <button class="lightbox__close" type="button" data-lightbox-close>
        <?= icon('close') ?><span class="visually-hidden">Tutup</span>
    </button>
    <div class="lightbox__stage" data-lightbox-stage></div>
    <p class="lightbox__caption" data-lightbox-caption></p>
</dialog>
