<?php
/**
 * Notification bell with a dropdown of the latest notifications.
 *
 * @var \Cake\View\Cell $this
 * @var list<\App\Model\Entity\Notification> $items
 * @var int $unread
 */
?>
<div class="dropdown">
    <button type="button" class="btn-ghost position-relative" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="<?= h(__('Notifications')) ?>">
        <i class="bi bi-bell" aria-hidden="true"></i>
        <?php if ($unread > 0) : ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" aria-hidden="true"><?= $unread > 9 ? '9+' : (int)$unread ?></span>
            <span class="visually-hidden"><?= h(__('{0} unread', $unread)) ?></span>
        <?php endif; ?>
    </button>

    <div class="dropdown-menu dropdown-menu-end p-0 shadow" style="width: 22rem; max-width: 92vw;">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <strong class="small"><?= __('Notifications') ?></strong>
            <?php if ($unread > 0) : ?>
                <?= $this->Form->create(null, ['url' => '/notifications/read-all', 'class' => 'm-0']) ?>
                    <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none"><?= __('Mark all as read') ?></button>
                <?= $this->Form->end() ?>
            <?php endif; ?>
        </div>

        <?php if ($items) : ?>
            <div style="max-height: 22rem; overflow-y: auto;">
                <?php foreach ($items as $n) : ?>
                    <?= $this->Form->create(null, ['url' => '/notifications/' . $n->id . '/open', 'class' => 'm-0']) ?>
                        <button type="submit" class="dropdown-item text-wrap px-3 py-2 border-bottom">
                            <span class="d-flex justify-content-between gap-2">
                                <span class="small <?= $n->read_at === null ? 'fw-semibold' : '' ?>"><?= h($n->headline()) ?></span>
                                <?php if ($n->read_at === null) : ?>
                                    <i class="bi bi-circle-fill text-danger flex-shrink-0" style="font-size: .5rem; margin-top: .4rem;" aria-hidden="true"></i>
                                    <span class="visually-hidden"><?= __('Unread') ?></span>
                                <?php endif; ?>
                            </span>
                            <span class="d-block small text-muted"><?= h($n->message()) ?></span>
                            <span class="d-block text-muted" style="font-size: .72rem;"><?= h($n->created->i18nFormat('dd MMM, h:mm a')) ?></span>
                        </button>
                    <?= $this->Form->end() ?>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="small text-muted px-3 py-3 mb-0"><?= __('You have no notifications yet.') ?></p>
        <?php endif; ?>

        <div class="text-center py-2">
            <a class="small fw-medium auth-link" href="<?= $this->Url->build('/notifications') ?>"><?= __('See all notifications') ?></a>
        </div>
    </div>
</div>