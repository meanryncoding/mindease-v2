<?php
/**
 * @var \App\View\AppView $this
 * @var string $name
 * @var string $initials
 */
$current = $this->getRequest()->getPath();
$items = [
    ['Overview', '/student', 'grid-1x2'],
    ['Wellness assessment', '#', 'clipboard2-pulse'],
    ['Results & progress', '#', 'graph-up'],
    ['Counselor feedback', '#', 'chat-left-text'],
    ['Support resources', '#', 'book'],
    ['Counseling sessions', '#', 'calendar-event'],
];
?>
<div class="d-flex align-items-center gap-2 mb-1">
    <?= $this->Html->image('mindease-logo.gif', ['alt' => '', 'class' => 'portal-logo']) ?>
    <span class="portal-brand">MindEase</span>
</div>
<p class="portal-org mb-2">UNIVERSITI TEKNOLOGI MARA</p>
<span class="workspace-pill">Student workspace</span>

<nav aria-label="Student navigation" class="mt-3">
    <ul class="list-unstyled mb-0 d-grid gap-1">
        <?php foreach ($items as [$label, $url, $icon]) : ?>
            <li>
                <a href="<?= h($url) ?>" class="nav-item-link"<?= $url === $current ? ' aria-current="page"' : '' ?>>
                    <i class="bi bi-<?= $icon ?>" aria-hidden="true"></i><?= h($label) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

<div class="mt-auto pt-4">
    <div class="support-card mb-3">
        <i class="bi bi-heart" aria-hidden="true"></i>
        <p class="fw-semibold mb-1 mt-1">You don't have to do it alone.</p>
        <a href="#" class="auth-link fw-medium text-decoration-none">Find urgent support <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="avatar" aria-hidden="true"><?= h($initials) ?></span>
        <div class="small lh-sm">
            <div class="fw-semibold"><?= h($name) ?></div>
            <a href="#" class="text-muted text-decoration-none">Account settings</a>
        </div>
        <a href="/logout" class="ms-auto text-muted" aria-label="Sign out"><i class="bi bi-box-arrow-right" aria-hidden="true"></i></a>
    </div>
</div>