<?php
/**
 * @var \App\View\AppView $this
 * @var string $name
 * @var string $initials
 */
$current = $this->getRequest()->getPath();
$items = [
    [__('Overview'), '/student', 'grid-1x2'],
    [__('Wellness assessment'), '#', 'clipboard2-pulse'],
    [__('Results & progress'), '#', 'graph-up'],
    [__('Counselor feedback'), '#', 'chat-left-text'],
    [__('Support resources'), '#', 'book'],
    [__('Counseling sessions'), '/student/sessions', 'calendar-event'],
];

// Pages that belong to a menu item, so it stays highlighted on its sub-pages.
$sections = ['/student/sessions' => ['/student/sessions', '/student/book']];
?>
<div class="d-flex align-items-center gap-2 mb-1">
    <?= $this->Html->image('mindease-logo.gif', ['alt' => '', 'class' => 'portal-logo']) ?>
    <span class="portal-brand">MindEase</span>
</div>
<p class="portal-org mb-2">UNIVERSITI TEKNOLOGI MARA</p>
<span class="workspace-pill"><?= __('Student workspace') ?></span>

<nav aria-label="<?= __('Student navigation') ?>" class="mt-3">
    <ul class="list-unstyled mb-0 d-grid gap-1">
        <?php foreach ($items as [$label, $url, $icon]) : ?>
            <?php
            $prefixes = $sections[$url] ?? [$url];
            $isActive = false;
            foreach ($prefixes as $prefix) {
                if ($current === $prefix || ($prefix !== '/student' && str_starts_with($current, $prefix . '/'))) {
                    $isActive = true;
                }
            }
            ?>
            <li>
                <a href="<?= h($url) ?>" class="nav-item-link"<?= $isActive ? ' aria-current="page"' : '' ?>>
                    <i class="bi bi-<?= $icon ?>" aria-hidden="true"></i><?= h($label) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

<div class="mt-auto pt-4">
    <div class="support-card mb-3">
        <i class="bi bi-heart" aria-hidden="true"></i>
        <p class="fw-semibold mb-1 mt-1"><?= __("You don't have to do it alone.") ?></p>
        <a href="#" class="auth-link fw-medium text-decoration-none"><?= __('Find urgent support') ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="avatar" aria-hidden="true"><?= h($initials) ?></span>
        <div class="small lh-sm">
            <div class="fw-semibold"><?= h($name) ?></div>
            <a href="#" class="text-muted text-decoration-none"><?= __('Account settings') ?></a>
        </div>
        <a href="/logout" class="ms-auto text-muted" aria-label="<?= __('Sign out') ?>"><i class="bi bi-box-arrow-right" aria-hidden="true"></i></a>
    </div>
</div>