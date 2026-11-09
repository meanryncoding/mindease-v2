<?php
/**
 * Unread updates for the student. Shows nothing when there are none.
 *
 * @var \Cake\View\Cell $this
 * @var list<\App\Model\Entity\Notification> $items
 * @var int $total
 */
if (!$items) {
    return;
}
?>
<section class="card-soft h-auto mb-3" aria-labelledby="updates-title">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h6 fw-semibold mb-0" id="updates-title"><?= __('Updates for you') ?></h2>
        <span class="chip"><?= (int)$total ?></span>
    </div>
    <div class="d-grid gap-2">
        <?php foreach ($items as $n) : ?>
            <?= $this->Form->create(null, ['url' => '/notifications/' . $n->id . '/open', 'class' => 'm-0']) ?>
                <button type="submit" class="session-row w-100 text-start">
                    <span>
                        <strong class="d-block"><?= h($n->headline()) ?></strong>
                        <span class="small"><?= h($n->message()) ?></span>
                        <?php if ($n->note()) : ?>
                            <span class="d-block small text-muted fst-italic">“<?= h($n->note()) ?>”</span>
                        <?php endif; ?>
                    </span>
                    <span class="chip"><?= __('New') ?></span>
                </button>
            <?= $this->Form->end() ?>
        <?php endforeach; ?>
    </div>
    <?php if ($total > count($items)) : ?>
        <a class="small fw-medium auth-link d-inline-block mt-2" href="<?= $this->Url->build('/notifications') ?>"><?= __('See all notifications') ?></a>
    <?php endif; ?>
</section>