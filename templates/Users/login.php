<?php
/** @var \App\View\AppView $this */
$this->assign('title', __('Sign in'));
$roles = [['student', __('Student'), 'mortarboard'], ['counselor', __('Counselor'), 'heart-pulse'], ['admin', __('Admin'), 'sliders']];
$partnerLine = __('An Institutional Partner');

// Image file names inside webroot/img (change here if an extension differs)
$smallLogo = 'mindease-logo.gif';
$heroLogo = 'mindease-logo2.png';
$uitmLogo = 'uitm-logo1.png';

// Floating light dots: [left %, size px, start offset s, duration s]
$dots = [[12, 6, 0, 11], [28, 4, 3, 14], [46, 7, 6, 12], [63, 4, 2, 15], [78, 6, 8, 10], [90, 5, 4, 13], [8, 4, 9, 16]];
?>
<div class="auth-wrap">

    <!-- Left panel: sign-in form -->
    <section class="auth-side auth-form-side">
        <div class="d-flex justify-content-between align-items-center small text-muted">
            <span><?= __('Student wellness portal') ?></span>
            <span class="d-flex align-items-center gap-3">
                <?= $this->element('lang_switch') ?>
                <button type="button" class="btn-ghost" data-theme-toggle aria-pressed="false" aria-label="<?= __('Dark mode') ?>">
                    <i class="bi bi-moon-stars theme-icon-light" aria-hidden="true"></i>
                    <i class="bi bi-sun theme-icon-dark" aria-hidden="true"></i>
                </button>
            </span>
        </div>

        <div class="auth-form-body">
            <h1 class="h4 fw-semibold mb-1"><?= __('Welcome to MindEase') ?></h1>
            <p class="text-muted small mb-3"><?= __('Choose your workspace and sign in to continue.') ?></p>

            <?php if (!empty($loginFailed)) : ?>
                <div class="alert alert-danger py-2 small" role="alert"><?= __('Incorrect email or password.') ?></div>
            <?php endif; ?>

            <?= $this->Form->create(null, ['url' => '/']) ?>
                <label class="form-label small fw-medium"><?= __("I'm signing in as a") ?></label>
                <div class="row g-2 mb-2">
                    <?php foreach ($roles as [$value, $label, $icon]) : ?>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="workspace" id="ws-<?= $value ?>" value="<?= $value ?>" <?= $value === 'student' ? 'checked' : '' ?>>
                            <label class="role-card" for="ws-<?= $value ?>"><i class="bi bi-<?= $icon ?>"></i><span><?= $label ?></span></label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <label class="form-label small fw-medium" for="email"><?= __('UiTM email address') ?></label>
                <input type="email" id="email" name="email" class="form-control mb-2" placeholder="aina.rahman@student.uitm.edu.my" value="<?= h($this->getRequest()->getData('email')) ?>" autocomplete="username" required>

                <label class="form-label small fw-medium" for="password"><?= __('Password') ?></label>
                <input type="password" id="password" name="password" class="form-control mb-2" placeholder="••••••••••••" autocomplete="current-password" required>

                <div class="d-flex justify-content-between align-items-center small mb-3">
                    <label class="form-check-label"><input type="checkbox" class="form-check-input me-2" checked><?= __('Remember this device') ?></label>
                    <a href="#" class="fw-semibold text-decoration-none auth-link"><?= __('Forgot password?') ?></a>
                </div>

                <button type="submit" class="btn btn-me w-100"><i class="bi bi-arrow-right me-2"></i><?= __('Sign in to your space') ?></button>
            <?= $this->Form->end() ?>

            <div class="privacy-box mt-3">
                <i class="bi bi-shield-check"></i>
                <div>
                    <strong><?= __('Your information stays protected') ?></strong>
                    <p class="mb-0"><?= __('Access is limited by role. Counseling notes are never visible to administrators or other students.') ?></p>
                </div>
            </div>
            <p class="help-line text-center small text-muted mt-3 mb-0"><?= __('Need help signing in?') ?> <a href="#" class="text-muted"><?= __('Visit the help centre') ?> →</a></p>
        </div>

        <div class="d-flex justify-content-between small text-muted">
            <a href="#" class="text-muted text-decoration-none"><?= h(__('Privacy & confidentiality')) ?></a>
            <a href="#" class="auth-link text-decoration-none fw-medium"><?= __('Need urgent support?') ?> →</a>
        </div>
    </section>

    <!-- Right panel: brand (animated background) -->
    <section class="auth-side auth-brand-side">
        <div class="brand-glow brand-glow-top" aria-hidden="true"></div>
        <div class="brand-glow brand-glow-low" aria-hidden="true"></div>
        <div class="brand-glow brand-glow-lotus" aria-hidden="true"></div>

        <div class="brand-dots" aria-hidden="true">
            <?php foreach ($dots as [$x, $size, $delay, $duration]) : ?>
                <span class="brand-dot" style="--x: <?= $x ?>%; --size: <?= $size ?>px; --delay: -<?= $delay ?>s; --dur: <?= $duration ?>s;"></span>
            <?php endforeach; ?>
        </div>

        <svg class="brand-wave wave-back" viewBox="0 0 1200 180" preserveAspectRatio="none" aria-hidden="true">
            <defs>
                <linearGradient id="waveGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#c9a4f2" stop-opacity=".38"/>
                    <stop offset="1" stop-color="#c9a4f2" stop-opacity=".04"/>
                </linearGradient>
            </defs>
            <path d="M0 100 Q150 40 300 100 T600 100 T900 100 T1200 100 L1200 180 L0 180 Z" fill="url(#waveGrad)"/>
            <path d="M0 100 Q150 40 300 100 T600 100 T900 100 T1200 100" fill="none" stroke="rgba(255,255,255,.22)" stroke-width="1.2"/>
        </svg>
        <svg class="brand-wave wave-front" viewBox="0 0 1200 180" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0 125 Q150 85 300 125 T600 125 T900 125 T1200 125 L1200 180 L0 180 Z" fill="url(#waveGrad)"/>
        </svg>

        <div class="brand-top">
            <div class="brand-lockup">
                <?= $this->Html->image($smallLogo, ['alt' => 'MindEase logo', 'class' => 'mindease-logo']) ?>
                <span class="brand-name">MindEase</span>
            </div>
            <div class="uitm-pill">
                <?= $this->Html->image($uitmLogo, ['alt' => 'Universiti Teknologi MARA', 'class' => 'uitm-logo']) ?>
                <span class="uitm-divider"></span>
                <span class="uitm-text"><?= h($partnerLine) ?><br>Universiti Teknologi MARA (UiTM)</span>
            </div>
        </div>

        <div class="brand-hero">
            <h2 class="brand-title"><?= __('Your Wellbeing') ?> <span class="brand-accent"><?= __('Matters.') ?></span></h2>
            <p class="brand-lead"><?= __('A little space to check in, find your balance, and connect with someone who cares.') ?></p>

            <div class="hero-logo-wrap">
                <?= $this->Html->image($heroLogo, ['alt' => 'MindEase', 'class' => 'mindease-hero-logo']) ?>
            </div>
        </div>

        <div class="brand-foot small">
            <div class="mb-1 fw-semibold"><i class="bi bi-shield-check me-2"></i><?= __('A private space. A kinder starting point.') ?></div>
            <div class="brand-note"><?= __('A conceptual student wellness experience. Sample records and scores are illustrative, not clinical diagnoses.') ?></div>
        </div>
    </section>
</div>