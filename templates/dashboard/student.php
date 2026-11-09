<?php
/** @var \App\View\AppView $this */
$this->assign('title', __('Overview'));
$firstName = explode(' ', trim((string)$this->Identity->get('name')))[0];

// SAMPLE DATA: replace with real data once assessments and appointments are connected.
$points = [['2026-09-02', 37], ['2026-09-16', 46], ['2026-09-30', 41], ['2026-10-14', 58], ['2026-10-28', 64]];
// SAMPLE DATA: the counselor note below is still a placeholder until counselor feedback is built.
$sampleCounselor = 'Nadia Hassan';

// Chart geometry
$width = 600;
$height = 170;
$padLeft = 30;
$padRight = 10;
$padTop = 10;
$padBottom = 24;
$plotWidth = $width - $padLeft - $padRight;
$plotHeight = $height - $padTop - $padBottom;
$baseY = $padTop + $plotHeight;

$coords = [];
foreach ($points as $i => [$date, $value]) {
    // Month names follow the active language (English or Malay).
    $label = (new \Cake\I18n\DateTime($date))->i18nFormat('dd MMM');
    $coords[] = [
        round($padLeft + $plotWidth * $i / (count($points) - 1), 1),
        round($padTop + $plotHeight * (1 - $value / 100), 1),
        $label,
        $value,
    ];
}
$line = implode(' ', array_map(fn($c) => $c[0] . ',' . $c[1], $coords));
$last = $coords[count($coords) - 1];
$first = $coords[0];
$area = $line . ' ' . $last[0] . ',' . $baseY . ' ' . $first[0] . ',' . $baseY;
$latest = $last[3];
$change = $latest - $coords[count($coords) - 2][3];
$counselorFirstName = explode(' ', $sampleCounselor)[0];
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
    <div>
        <h1 class="h3 fw-semibold mb-1"><?= h(__('A little space for you, {0}.', $firstName)) ?></h1>
        <p class="text-muted small mb-0"><?= (new \Cake\I18n\DateTime())->i18nFormat('EEEE, d MMMM y') ?> · <?= __("Let's take today one step at a time.") ?></p>
    </div>
    <a href="#" class="btn btn-me px-4"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i><?= __('Take a check-in') ?></a>
</div>

<?= $this->cell('StudentUpdates') ?>

<div class="row g-3">
    <div class="col-lg-8">
        <section class="card-soft card-hero d-flex align-items-center justify-content-between gap-3" aria-labelledby="hero-title">
            <div>
                <p class="eyebrow mb-2"><?= __('YOUR WELLBEING MATTERS') ?></p>
                <h2 id="hero-title" class="h3 fw-semibold mb-2"><?= __('Small steps. Meaningful change.') ?></h2>
                <p class="text-muted small mb-3"><?= __('Pause, notice how you feel, and make room for what you need.') ?></p>
                <a href="#" class="auth-link fw-medium text-decoration-none small"><?= __('Explore your wellbeing toolkit') ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
            <?= $this->Html->image('mindease-logo2.png', ['alt' => '', 'class' => 'hero-card-img d-none d-md-block']) ?>
        </section>
    </div>

    <div class="col-lg-4">
        <?= $this->cell('NextSession') ?>
    </div>

    <div class="col-lg-8">
        <section class="card-soft" aria-labelledby="wellbeing-title">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 id="wellbeing-title" class="h6 fw-semibold mb-1"><?= __('Your wellbeing, over time') ?></h2>
                    <p class="small text-muted mb-2"><?= __('A personal reflection, not a clinical diagnosis.') ?></p>
                </div>
                <a href="#" class="auth-link small fw-medium text-decoration-none"><?= __('View results') ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="stat-number"><?= $latest ?></span>
                <span class="chip chip-up"><?= $change >= 0 ? '↑' : '↓' ?> <?= h(__('{0} points since last check-in', abs($change))) ?></span>
                <span class="chip"><?= __('Sample data') ?></span>
            </div>
            <p class="small text-muted mb-1"><?= __('Higher = more positive wellbeing') ?></p>

                        <svg viewBox="0 0 <?= $width ?> <?= $height ?>" class="w-100" role="img" aria-labelledby="chart-title chart-desc">
                <title id="chart-title"><?= __('Wellbeing score over time') ?></title>
                <desc id="chart-desc"><?= h(__('Line chart of sample wellbeing scores from {0} ({1}) to {2} ({3}). The same values are listed in a table for screen readers.', $first[2], $first[3], $last[2], $latest)) ?></desc>
                <?php foreach ([0, 50, 100] as $g) :
                    $gy = round($padTop + $plotHeight * (1 - $g / 100), 1); ?>
                    <line class="chart-grid" x1="<?= $padLeft ?>" y1="<?= $gy ?>" x2="<?= $width - $padRight ?>" y2="<?= $gy ?>"/>
                    <text class="chart-label" x="<?= $padLeft - 6 ?>" y="<?= $gy + 3 ?>" text-anchor="end"><?= $g ?></text>
                <?php endforeach; ?>
                <polygon class="chart-area" points="<?= $area ?>"/>
                <polyline class="chart-line" points="<?= $line ?>"/>
                <?php foreach ($coords as $i => $c) : ?>
                    <circle class="chart-dot" cx="<?= $c[0] ?>" cy="<?= $c[1] ?>" r="<?= $i === count($coords) - 1 ? 4 : 2.5 ?>"/>
                    <text class="chart-label" x="<?= $c[0] ?>" y="<?= $height - 6 ?>" text-anchor="middle"><?= h($c[2]) ?></text>
                <?php endforeach; ?>
            </svg>

            <table class="visually-hidden">
                <caption><?= __('Sample wellbeing scores') ?></caption>
                <thead><tr><th scope="col"><?= __('Date') ?></th><th scope="col"><?= __('Score') ?></th></tr></thead>
                <tbody>
                    <?php foreach ($coords as $c) : ?>
                        <tr><td><?= h($c[2]) ?></td><td><?= $c[3] ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>

    <div class="col-lg-4">
        <section class="card-soft card-calm" aria-labelledby="pause-title">
            <i class="bi bi-wind fs-5" aria-hidden="true"></i>
            <h2 id="pause-title" class="h6 fw-semibold mt-2 mb-2"><?= __('Take a gentle pause') ?></h2>
            <p class="small mb-3"><?= __('Try a two-minute breathing exercise between your classes.') ?></p>
            <a href="#" class="fw-medium text-decoration-none small link-green"><?= __('Start breathing exercise') ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </section>
    </div>

    <div class="col-12">
        <section class="card-soft d-flex flex-wrap align-items-center justify-content-between gap-3" aria-labelledby="note-title">
            <div class="d-flex align-items-center gap-3">
                <span class="avatar" aria-hidden="true">NH</span>
                <div>
                    <h2 id="note-title" class="h6 fw-semibold mb-1"><?= h(__('A note from {0}', $counselorFirstName)) ?></h2>
                    <p class="small text-muted mb-0">"<?= __("You've made space to reflect this week. Let's keep building a routine that feels manageable.") ?>"</p>
                </div>
            </div>
            <a href="#" class="btn btn-outline-secondary btn-sm"><?= __('Read feedback') ?></a>
        </section>
    </div>
</div>