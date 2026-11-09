<?php
/** @var \App\View\AppView $this */
$name = (string)$this->Identity->get('name');
$email = (string)$this->Identity->get('email');
$parts = preg_split('/\s+/', trim($name)) ?: [];
$initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
$navVars = ['name' => $name, 'initials' => $initials];
?>
<!DOCTYPE html>
<html lang="<?= h($lang ?? 'en') ?>">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MindEase: <?= $this->fetch('title') ?></title>
    <link rel="icon" type="image/png" href="<?= $this->Url->image('favicon.png?v=2') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Fraunces:wght@500;600&family=Atkinson+Hyperlegible:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <?= $this->Html->css('mindease.css?v=6') ?>
    <?= $this->Html->css('counselor.css?v=1') ?>
    <?= $this->Html->script('accessibility.js?v=2') ?>
    <?= $this->fetch('css') ?>
</head>
<body class="portal-body">
    <a class="skip-link" href="#main"><?= __('Skip to main content') ?></a>

    <div class="portal">
        <aside class="portal-sidebar d-none d-lg-flex" aria-label="<?= __('Sidebar') ?>">
            <?= $this->element('counselor_nav', $navVars) ?>
        </aside>

        <div class="portal-main">
            <header class="portal-topbar">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn-ghost d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-label="<?= __('Open menu') ?>">
                        <i class="bi bi-list" aria-hidden="true"></i>
                    </button>
                    <nav aria-label="<?= __('Breadcrumb') ?>">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item"><?= __('Counselor portal') ?></li>
                            <li class="breadcrumb-item active" aria-current="page"><?= h($this->fetch('title')) ?></li>
                        </ol>
                    </nav>
                </div>

                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <span class="small text-muted d-none d-xl-inline"><i class="bi bi-lock" aria-hidden="true"></i> <?= __('Private and confidential') ?></span>
                    <span class="small d-none d-sm-inline"><?= $this->element('lang_switch') ?></span>
                    <button type="button" class="btn-ghost" data-theme-toggle aria-pressed="false" aria-label="<?= __('Dark mode') ?>">
                        <i class="bi bi-moon-stars theme-icon-light" aria-hidden="true"></i>
                        <i class="bi bi-sun theme-icon-dark" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="btn-ghost" data-bs-toggle="offcanvas" data-bs-target="#a11yPanel" aria-controls="a11yPanel">
                        <i class="bi bi-universal-access" aria-hidden="true"></i>
                        <span class="visually-hidden"><?= __('Accessibility settings') ?></span>
                    </button>
                    <button type="button" class="btn-ghost" aria-label="<?= __('Notifications') ?>">
                        <i class="bi bi-bell" aria-hidden="true"></i>
                    </button>
                    <div class="dropdown">
                        <button class="avatar" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="<?= h(__('Account menu for {0}', $name)) ?>"><?= h($initials) ?></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small text-muted"><?= h($email) ?></span></li>
                            <li><a class="dropdown-item" href="<?= $this->Url->build('/logout') ?>"><?= __('Sign out') ?></a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <main id="main" tabindex="-1" class="portal-content">
                <?php $notice = $this->getRequest()->getSession()->consume('notice'); ?>
                <?php if (is_array($notice) && !empty($notice['text'])) : ?>
                    <div class="alert alert-<?= ($notice['type'] ?? '') === 'success' ? 'success' : 'danger' ?> py-2 small" role="status">
                        <?= h($notice['text']) ?>
                    </div>
                <?php endif; ?>
                <?= $this->fetch('content') ?>
            </main>

            <footer class="portal-footer small text-muted">
                <span>MindEase · <?= __('Your wellbeing matters.') ?></span>
                <span><a href="#" class="text-muted"><?= __('Privacy and confidentiality') ?></a> · <a href="#" class="text-muted"><?= __('Help centre') ?></a></span>
            </footer>
        </div>
    </div>

    <!-- Mobile navigation -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNav" aria-label="<?= __('Menu') ?>">
        <div class="offcanvas-header">
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="<?= __('Close menu') ?>"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <?= $this->element('counselor_nav', $navVars) ?>
        </div>
    </div>

    <!-- Display and accessibility settings -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="a11yPanel" aria-labelledby="a11yTitle">
        <div class="offcanvas-header">
            <h2 class="offcanvas-title h5" id="a11yTitle"><?= __('Accessibility settings') ?></h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="<?= __('Close accessibility settings') ?>"></button>
        </div>
        <div class="offcanvas-body">
            <p class="small text-muted"><?= __('These settings are saved on this device.') ?></p>

            <fieldset class="mb-4">
                <legend class="h6"><?= __('Language') ?></legend>
                <?= $this->element('lang_switch') ?>
            </fieldset>

            <fieldset class="mb-4">
                <legend class="h6"><?= __('Theme') ?></legend>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="a11yTheme" id="a11yThemeSystem" value="system">
                    <label class="form-check-label" for="a11yThemeSystem"><?= __('Match my device') ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="a11yTheme" id="a11yThemeLight" value="light">
                    <label class="form-check-label" for="a11yThemeLight"><?= __('Light') ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="a11yTheme" id="a11yThemeDark" value="dark">
                    <label class="form-check-label" for="a11yThemeDark"><?= __('Dark') ?></label>
                </div>
            </fieldset>

            <fieldset class="mb-4">
                <legend class="h6"><?= __('Text size') ?></legend>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-a11y-size="down" aria-label="<?= __('Decrease text size') ?>">A−</button>
                    <span class="flex-grow-1 text-center fw-semibold" id="a11ySizeLabel" aria-live="polite">100%</span>
                    <button type="button" class="btn btn-outline-secondary" data-a11y-size="up" aria-label="<?= __('Increase text size') ?>">A+</button>
                </div>
            </fieldset>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="a11yContrast">
                <label class="form-check-label" for="a11yContrast"><?= __('High contrast (always light)') ?></label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="a11yFont">
                <label class="form-check-label" for="a11yFont"><?= __('Easy-to-read font') ?></label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="a11ySpacing">
                <label class="form-check-label" for="a11ySpacing"><?= __('Wider text spacing') ?></label>
            </div>
            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" role="switch" id="a11yMotion">
                <label class="form-check-label" for="a11yMotion"><?= __('Reduce motion') ?></label>
            </div>

            <button type="button" class="btn btn-outline-secondary w-100" id="a11yReset"><?= __('Reset to default') ?></button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->fetch('script') ?>
</body>
</html>