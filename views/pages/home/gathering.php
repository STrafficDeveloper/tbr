<?php

declare(strict_types=1);

/** @var \App\Repositories\SiteRepository $site */
$features = [
    ['icon' => 'users', 'title' => 'Ukhwah Biker', 'text' => $site->setting('pitstop_intro', 'Satu pitstop, banyak cerita, jom eratkan hubungan bersama!')],
    ['icon' => 'gift', 'title' => 'Ganjaran & Hadiah Menarik', 'text' => 'Bawa pulang hadiah istimewa tanda kenang-kenangan.'],
];
?>
<section class="gathering" aria-labelledby="gathering-title">
    <div class="container">
        <h2 class="gathering__title" id="gathering-title">
            <?= e($site->setting('home_gathering_title', 'Bukan race. Bukan rally.')) ?>
            <span class="gathering__highlight"><?= e($site->setting('home_gathering_highlight', 'Ini gathering.')) ?></span>
        </h2>
        <ul class="gathering__features">
<?php foreach ($features as $feature): ?>
            <li class="gathering__feature">
                <span class="gathering__icon"><?= icon($feature['icon']) ?></span>
                <div>
                    <h3 class="gathering__feature-title"><?= e($feature['title']) ?></h3>
                    <p><?= e($feature['text']) ?></p>
                </div>
            </li>
<?php endforeach; ?>
        </ul>
    </div>
</section>
