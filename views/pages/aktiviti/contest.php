<?php

declare(strict_types=1);

/**
 * The design's peraduan pop-up, as a page.
 *
 * @var array<string,mixed> $contest
 * @var string $phase ongoing | upcoming | ended
 * @var list<array<string,mixed>> $prizes
 * @var list<string> $steps
 * @var string $whatsappUrl
 */
$phaseLabels = ['ongoing' => 'Sedang Berlangsung', 'upcoming' => 'Akan Datang', 'ended' => 'Tamat'];
?>
<article class="section" aria-labelledby="page-title">
    <div class="container contest">
        <?= component('back-link', ['url' => '/aktiviti/peraduan']) ?>

        <header class="contest__header">
            <p class="section-heading__eyebrow">aktiviti TBR</p>
            <h1 class="contest__title" id="page-title"><?= e($contest['title']) ?></h1>
            <p class="contest__dates">
                <?= icon('calendar', 'icon icon--sm') ?>
                <?= e(formatDateRange((string) $contest['starts_on'], (string) $contest['ends_on'])) ?>
                <span class="status status--<?= $phase === 'ongoing' ? 'approved' : 'pending' ?>"><?= e($phaseLabels[$phase]) ?></span>
            </p>
<?php if (!empty($contest['tagline'])): ?>
            <p class="lead-text"><?= e((string) $contest['tagline']) ?></p>
<?php endif; ?>
        </header>

        <div class="contest__body">
            <section class="contest__block">
                <h2>Tempoh Peraduan</h2>
                <p><?= e(formatDateRange((string) $contest['starts_on'], (string) $contest['ends_on'])) ?></p>
            </section>

<?php if (!empty($contest['announce_on'])): ?>
            <section class="contest__block">
                <h2>Pengumuman Pemenang</h2>
                <p><?= e(formatDate((string) $contest['announce_on'])) ?></p>
            </section>
<?php endif; ?>

<?php if (!empty($contest['eligibility'])): ?>
            <section class="contest__block">
                <h2>Kelayakan Penyertaan</h2>
                <p><?= nl2br(e((string) $contest['eligibility'])) ?></p>
            </section>
<?php endif; ?>

<?php if ($prizes !== []): ?>
            <section class="contest__block">
                <h2>Senarai Hadiah</h2>
                <ul class="prize-list">
<?php foreach ($prizes as $prize): ?>
                    <li>
                        <span class="prize-list__rank"><?= e($prize['rank_label']) ?></span>
                        <span><?= e($prize['prize_name']) ?><?= !empty($prize['prize_value']) ? ' — ' . e($prize['prize_value']) : '' ?></span>
                    </li>
<?php endforeach; ?>
                </ul>
            </section>
<?php endif; ?>

<?php if ($steps !== []): ?>
            <section class="contest__block">
                <h2>Cara Sertai</h2>
                <ol class="steps">
<?php foreach ($steps as $step): ?>
                    <li><?= e($step) ?></li>
<?php endforeach; ?>
                </ol>
            </section>
<?php endif; ?>
        </div>

        <div class="contest__cta">
<?php if ($contest['winners_announced']): ?>
            <a class="btn btn--primary" href="/aktiviti/pemenang/<?= e(rawurlencode((string) $contest['slug'])) ?>">Lihat Pemenang</a>
<?php elseif ($phase === 'ongoing'): ?>
            <a class="btn btn--primary" href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener noreferrer">
                <?= icon('whatsapp') ?> WhatsApp Sekarang<span class="visually-hidden"> (dibuka di tab baharu)</span>
            </a>
<?php elseif ($phase === 'upcoming'): ?>
            <p class="contest__note">Penyertaan dibuka pada <?= e(formatDate((string) $contest['starts_on'])) ?>.</p>
<?php else: ?>
            <p class="contest__note">Peraduan ini telah tamat. Pemenang akan diumumkan<?= !empty($contest['announce_on']) ? ' pada ' . e(formatDate((string) $contest['announce_on'])) : ' tidak lama lagi' ?>.</p>
<?php endif; ?>
            <a class="btn btn--ghost" href="/aktiviti/peraduan">Kembali</a>
        </div>
    </div>
</article>
