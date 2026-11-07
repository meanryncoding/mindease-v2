<?php
/** @var \App\View\AppView $this */
$name = (string)$this->Identity->get('name');
$email = (string)$this->Identity->get('email');
$parts = preg_split('/\s+/', trim($name)) ?: [];
$initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
$navVars = ['name' => $name, 'initials' => $initials];
?>
<!DOCTYPE html>
<html lang="en">
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
    <?= $this->Html->css('mindease.css?v=4') ?>
    <?= $this->Html->script('accessibility.js?v=1') ?>
    <?= $this->fetch('css') ?>
</head>
<body class="portal-body">
    <a class="skip-link" href="#main">Skip to main content</a>

    <div class="portal">
        <aside class="portal-sidebar d-none d-lg-flex" aria-label="Sidebar">
            <?= $this->element('student_nav', $navVars) ?>
        </aside>

        <div class="portal-main">
            <header class="portal-topbar">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-light d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-label="Open menu">
                        <i class="bi bi-list" aria-hidden="true"></i>
                    </button>
                    <nav aria-label="Breadcrumb">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item">Student portal</li>
                            <li class="breadcrumb-item active" aria-current="page"><?= h($this->fetch('title')) ?></li>
                        </ol>
                    </nav>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <span class="small text-muted d-none d-md-inline"><i class="bi bi-lock" aria-hidden="true"></i> Private &amp; confidential</span>
                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="offcanvas" data-bs-target="#a11yPanel" aria-controls="a11yPanel">
                        <i class="bi bi-universal-access" aria-hidden="true"></i>
                        <span class="visually-hidden">Accessibility settings</span>
                    </button>
                    <button type="button" class="btn btn-light btn-sm" aria-label="Notifications">
                        <i class="bi bi-bell" aria-hidden="true"></i>
                    </button>
                    <div class="dropdown">
                        <button class="avatar" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account menu for <?= h($name) ?>"><?= h($initials) ?></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small text-muted"><?= h($email) ?></span></li>
                            <li><a class="dropdown-item" href="/logout">Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <main id="main" tabindex="-1" class="portal-content">
                <?= $this->fetch('content') ?>
            </main>

            <footer class="portal-footer small text-muted">
                <span>MindEase · Your Wellbeing Matters.</span>
                <span><a href="#" class="text-muted">Privacy &amp; confidentiality</a> · <a href="#" class="text-muted">Help centre</a></span>
            </footer>
        </div>
    </div>

    <!-- Mobile navigation -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNav" aria-label="Menu">
        <div class="offcanvas-header">
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close menu"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <?= $this->element('student_nav', $navVars) ?>
        </div>
    </div>

    <!-- Accessibility settings -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="a11yPanel" aria-labelledby="a11yTitle">
        <div class="offcanvas-header">
            <h2 class="offcanvas-title h5" id="a11yTitle">Accessibility settings</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close accessibility settings"></button>
        </div>
        <div class="offcanvas-body">
            <p class="small text-muted">These settings are saved on this device.</p>

            <fieldset class="mb-4">
                <legend class="h6">Text size</legend>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-a11y-size="down" aria-label="Decrease text size">A−</button>
                    <span class="flex-grow-1 text-center fw-semibold" id="a11ySizeLabel" aria-live="polite">100%</span>
                    <button type="button" class="btn btn-outline-secondary" data-a11y-size="up" aria-label="Increase text size">A+</button>
                </div>
            </fieldset>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="a11yContrast">
                <label class="form-check-label" for="a11yContrast">High contrast</label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="a11yFont">
                <label class="form-check-label" for="a11yFont">Easy-to-read font</label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="a11ySpacing">
                <label class="form-check-label" for="a11ySpacing">Wider text spacing</label>
            </div>
            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" role="switch" id="a11yMotion">
                <label class="form-check-label" for="a11yMotion">Reduce motion</label>
            </div>

            <button type="button" class="btn btn-outline-secondary w-100" id="a11yReset">Reset to default</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->fetch('script') ?>
</body>
</html>