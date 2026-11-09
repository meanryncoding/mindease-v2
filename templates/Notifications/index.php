<?php
/**
 * All notifications for the signed-in user.
 *
 * @var \App\View\AppView $this
 * @var list<\App\Model\Entity\Notification> $notifications
 */
$this->assign('title', __('Notifications'));
$unread = count(array_filter($notifications, static fn($n) => $n->read_at === null));
?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
    <div>
        <h1 class="h3 fw-semibold mb-1"><?= __('Notifications') ?></h1>
        <p class="text-muted small mb-0"><?= __('Updates about your appointments.') ?></p>
    </div>
    <?php if ($unread > 0) : ?>
        <?= $this->Form->create(null, ['url' => '/notifications/read-all']) ?>
            <button type="submit" class="btn btn-outline-secondary btn-sm"><?= __('Mark all as read') ?></button>
        <?= $this->Form->end() ?>
    <?php endif; ?>
</div>

<section class="card-soft h-auto" aria-label="<?= __('Notifications') ?>">
    <?php if ($notifications) : ?>
        <div class="d-grid gap-2">
            <?php foreach ($notifications as $n) : ?>
                <?= $this->Form->create(null, ['url' => '/notifications/' . $n->id . '/open', 'class' => 'm-0']) ?>
                    <button type="submit" class="session-row w-100 text-start<?= $n->read_at === null ? ' is-selected' : '' ?>">
                        <span>
                            <strong class="d-block"><?= h($n->headline()) ?></strong>
                            <span class="small"><?= h($n->message()) ?></span>
                            <?php if ($n->note()) : ?>
                                <span class="d-block small text-muted fst-italic">“<?= h($n->note()) ?>”</span>
                            <?php endif; ?>
                            <span class="d-block small text-muted"><?= h($n->created->i18nFormat('EEE, dd MMM y · h:mm a')) ?></span>
                        </span>
                        <?php if ($n->read_at === null) : ?>
                            <span class="chip"><?= __('New') ?></span>
                        <?php endif; ?>
                    </button>
                <?= $this->Form->end() ?>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p class="small text-muted mb-0"><?= __('You have no notifications yet.') ?></p>
    <?php endif; ?>
</section>