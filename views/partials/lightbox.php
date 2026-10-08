<?php

declare(strict_types=1);

use App\Repositories\BannerRepository;

/**
 * One <dialog> per page for the design's "pop up" views: photos, videos, and
 * the event / bike shop details (cloned from a <template> in each card).
 * <dialog> gives focus trapping, Escape-to-close and a backdrop natively.
 * Detail pop-ups show the "popup" sponsor banner underneath, as designed.
 */
$promo = (new BannerRepository())->forPlacement('popup')[0] ?? null;
?>
<dialog class="lightbox" data-lightbox aria-label="Paparan media">
    <button class="lightbox__close" type="button" data-lightbox-close>
        <?= icon('close') ?><span class="visually-hidden">Tutup</span>
    </button>
    <div class="lightbox__stage" data-lightbox-stage></div>
    <p class="lightbox__caption" data-lightbox-caption></p>
<?php if ($promo !== null): ?>
    <div class="lightbox__promo" data-lightbox-promo hidden>
        <?= component('promo-banner', ['banner' => $promo]) ?>
    </div>
<?php endif; ?>
</dialog>
