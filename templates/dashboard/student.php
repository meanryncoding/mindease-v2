<?php
/** @var \App\View\AppView $this */
$this->assign('title', 'Overview');
$firstName = explode(' ', trim((string)$this->Identity->get('name')))[0];

// SAMPLE DATA: replace with real data once assessments and appointments are connected.
$points = [['02 Sep', 37], ['16 Sep', 46], ['30 Sep', 41], ['14 Oct', 58], ['28 Oct', 64]];
$session = [
    'counselor' => 'Nadia Hassan',
    'role' => 'Student wellbeing counselor',
    'when' => date('D, d M Y', strtotime('+3 days')) . ' · 10:00–10:50 AM',
    'mode' => 'Online · Private video session',
];

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
foreach ($points as $i => [$label, $value]) {
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
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
    <div>
        <h1 class="h3 fw-semibold mb-1">A little space for you, <?= h($firstName) ?>.</h1>
        <p class="text-muted small mb-0"><?= date('l, j F Y') ?> · Let's take today one step at a time.</p>
    </div>
    <a href="#" class="btn btn-me px-4"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Take a check-in</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <section class="card-soft card-hero d-flex align-items-center justify-content-between gap-3" aria-labelledby="hero-title">
            <div>
                <p class="eyebrow mb-2">YOUR WELLBEING MATTERS</p>
                <h2 id="hero-title" class="h3 fw-semibold mb-2">Small steps. Meaningful change.</h2>
                <p class="text-muted small mb-3">Pause, notice how you feel, and make room for what you need.</p>
                <a href="#" class="auth-link fw-medium text-decoration-none small">Explore your wellbeing toolkit <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
            <?= $this->Html->image('mindease-logo2.png', ['alt' => '', 'class' => 'hero-card-img d-none d-md-block']) ?>
        </section>
    </div>

    <div class="col-lg-4">
        <section class="card-soft" aria-labelledby="session-title">
            <h2 id="session-title" class="h6 fw-semibold mb-3">Your next session</h2>
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="avatar" aria-hidden="true">NH</span>
                <div class="small lh-sm">
                    <div class="fw-semibold"><?= h($session['counselor']) ?></div>
                    <div class="text-muted"><?= h($session['role']) ?></div>
                </div>
            </div>
            <p class="small mb-1"><?= h($session['when']) ?></p>
            <p class="small text-muted mb-3"><?= h($session['mode']) ?></p>
            <a href="#" class="btn btn-outline-secondary w-100 btn-sm">View appointment</a>
        </section>
    </div>

    <div class="col-lg-8">
        <section class="card-soft" aria-labelledby="wellbeing-title">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 id="wellbeing-title" class="h6 fw-semibold mb-1">Your wellbeing, over time</h2>
                    <p class="small text-muted mb-2">A personal reflection, not a clinical diagnosis.</p>
                </div>
                <a href="#" class="auth-link small fw-medium text-decoration-none">View results <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="stat-number"><?= $latest ?></span>
                <span class="chip chip-up"><?= $change >= 0 ? '↑' : '↓' ?> <?= abs($change) ?> points since last check-in</span>
                <span class="chip">Sample data</span>
            </div>
            <p class="small text-muted mb-1">Higher = more positive wellbeing</p>

            <svg viewBox="0 0 <?= $width ?> <?= $height ?>" class="w-100" role="img" aria-labelledby="chart-title chart-desc">
                <title id="chart-title">Wellbeing score over time</title>
                <desc id="chart-desc">Line chart of sample wellbeing scores from <?= h($first[2]) ?> (<?= $first[3] ?>) to <?= h($last[2]) ?> (<?= $latest ?>). The same values are listed in a table for screen readers.</desc>
                <?php foreach ([0, 50, 100] as $g) :
                    $gy = round($padTop + $plotHeight * (1 - $g / 100), 1); ?>
                    <line x1="<?= $padLeft ?>" y1="<?= $gy ?>" x2="<?= $width - $padRight ?>" y2="<?= $gy ?>" stroke="#e4e1ea" stroke-width="1"/>
                    <text x="<?= $padLeft - 6 ?>" y="<?= $gy + 3 ?>" text-anchor="end" font-size="9" fill="#6f687a"><?= $g ?></text>
                <?php endforeach; ?>
                <polygon points="<?= $area ?>" fill="rgba(155,131,196,.18)"/>
                <polyline points="<?= $line ?>" fill="none" stroke="#9b83c4" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                <?php foreach ($coords as $i => $c) : ?>
                    <circle cx="<?= $c[0] ?>" cy="<?= $c[1] ?>" r="<?= $i === count($coords) - 1 ? 4 : 2.5 ?>" fill="#6b4fa0"/>
                    <text x="<?= $c[0] ?>" y="<?= $height - 6 ?>" text-anchor="middle" font-size="9" fill="#6f687a"><?= h($c[2]) ?></text>
                <?php endforeach; ?>
            </svg>

            <table class="visually-hidden">
                <caption>Sample wellbeing scores</caption>
                <thead><tr><th scope="col">Date</th><th scope="col">Score</th></tr></thead>
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
            <h2 id="pause-title" class="h6 fw-semibold mt-2 mb-2">Take a gentle pause</h2>
            <p class="small mb-3">Try a two-minute breathing exercise between your classes.</p>
            <a href="#" class="fw-medium text-decoration-none small" style="color:#2f5d3a">Start breathing exercise <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </section>
    </div>

    <div class="col-12">
        <section class="card-soft d-flex flex-wrap align-items-center justify-content-between gap-3" aria-labelledby="note-title">
            <div class="d-flex align-items-center gap-3">
                <span class="avatar" aria-hidden="true">NH</span>
                <div>
                    <h2 id="note-title" class="h6 fw-semibold mb-1">A note from Nadia</h2>
                    <p class="small text-muted mb-0">"You've made space to reflect this week. Let's keep building a routine that feels manageable."</p>
                </div>
            </div>
            <a href="#" class="btn btn-outline-secondary btn-sm">Read feedback</a>
        </section>
    </div>
</div>