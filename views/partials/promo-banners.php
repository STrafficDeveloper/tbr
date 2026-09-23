<?php

declare(strict_types=1);

use App\Repositories\BannerRepository;

$banners = (new BannerRepository())->forPlacement('global');

if ($banners === []) {
    return;
}
?>
<section class="promo-banners" aria-label="Promosi">
    <div class="container promo-banners__list">
<?php foreach ($banners as $banner): ?>
        <?= component('promo-banner', ['banner' => $banner]) ?>
<?php endforeach; ?>
    </div>
</section>
