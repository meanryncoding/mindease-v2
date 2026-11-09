<?php
/**
 * Counselor navigation (used in the desktop sidebar and the mobile menu).
 *
 * @var \App\View\AppView $this
 * @var string $name
 * @var string $initials
 */
$path = rtrim($this->getRequest()->getPath(), '/') ?: '/';
$items = [
    ['label' => __('Care overview'), 'url' => '/counselor', 'icon' => 'bi-house-heart', 'exact' => true],
    ['label' => __('Student caseload'), 'url' => '#', 'icon' => 'bi-people'],
    ['label' => __('Clinical notes'), 'url' => '#', 'icon' => 'bi-journal-text'],
    ['label' => __('Appointments'), 'url' => '/counselor/appointments', 'icon' => 'bi-calendar-week'],
];
?>
<div class="d-flex align-items-center gap-2 mb-4">
    <img src="<?= $this->Url->image('mindease-logo.gif') ?>" alt="" class="portal-logo">
    <div>
        <div class="portal-brand">MindEase</div>
        <div class="portal-org">UiTM</div>
    </div>
</div>

<span class="workspace-pill mb-3 align-self-start"><?= __('Counselor workspace') ?></span>

<nav aria-label="<?= __('Main navigation') ?>" class="flex-grow-1">
    <ul class="list-unstyled d-grid gap-1 mb-0">
        <?php foreach ($items as $item) :
            $url = $item['url'];
            if ($url === '#') {
                $active = false;
            } elseif (!empty($item['exact'])) {
                $active = $path === $url;
            } else {
                $active = $path === $url || str_starts_with($path, $url . '/');
            } ?>
            <li>
                <a class="nav-item-link" href="<?= $url === '#' ? '#' : $this->Url->build($url) ?>"<?= $active ? ' aria-current="page"' : '' ?>>
                    <i class="bi <?= h($item['icon']) ?>" aria-hidden="true"></i>
                    <span><?= h($item['label']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

<div class="support-card my-3">
    <strong class="d-block mb-1"><?= __('Records are confidential') ?></strong>
    <span><?= __('Open a student record only when it is needed for their care.') ?></span>
</div>

<div class="d-flex align-items-center gap-2 pt-3 border-top">
    <span class="avatar" aria-hidden="true"><?= h($initials) ?></span>
    <div class="flex-grow-1 small">
        <div class="fw-medium"><?= h($name) ?></div>
        <a href="<?= $this->Url->build('/logout') ?>" class="text-muted text-decoration-none"><?= __('Sign out') ?></a>
    </div>
</div>