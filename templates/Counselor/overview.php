<?php
/**
 * Counselor home: "Care overview".
 *
 * @var \App\View\AppView $this
 * @var int $pendingCount
 * @var int $weekCount
 * @var iterable<\App\Model\Entity\Appointment> $todaySessions
 * @var list<array{appointment: \App\Model\Entity\Appointment, waited: int, until: int, level: string, urgent: bool}> $requests
 * @var list<\App\Model\Entity\Notification> $studentUpdates
 */
$this->assign('title', __('Care overview'));

$fullName = (string)$this->Identity->get('name');
$nameParts = preg_split('/\s+/', trim($fullName)) ?: [];
$firstName = $nameParts[0] ?? '';

$todayList = is_array($todaySessions) ? $todaySessions : iterator_to_array($todaySessions, false);
$attentionCount = count($requests) + count($studentUpdates);

// Chip text for each kind of student update.
$updateLabels = [
    'appointment_cancelled' => __('Cancelled'),
    'proposal_accepted' => __('Accepted'),
    'proposal_declined' => __('Declined'),
];

// "3 hr" or "2 d" from a number of hours.
$span = static function (int $hours): string {
    if ($hours < 1) {
        return __('less than 1 hr');
    }
    if ($hours < 48) {
        return __('{0} hr', $hours);
    }

    return __('{0} d', intdiv($hours, 24));
};

$todayRow = function (\App\Model\Entity\Appointment $a): string {
    $url = $this->Url->build('/counselor/appointments?appt=' . $a->id);

    return '<a class="session-row" href="' . h($url) . '"><span><strong class="d-block">'
        . h((string)$a->student->name) . '</strong><span class="small text-muted">'
        . h($a->requested_start->i18nFormat('h:mm a')) . ' · ' . h($a->modeLabel())
        . '</span></span><span class="' . h($a->status->chipClass()) . '">' . h($a->status->label()) . '</span></a>';
};
?>

<h1 class="h3 fw-semibold mb-1"><?= h(__('Welcome back, {0}', $firstName)) ?></h1>
<p class="text-muted mb-4"><?= __('Here is what needs your attention today.') ?></p>

<div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-xl-3">
        <a class="card-soft h-100 d-block text-decoration-none stat-link" href="<?= $this->Url->build('/counselor/appointments') ?>">
            <div class="eyebrow text-uppercase mb-1"><?= __('Pending requests') ?></div>
            <div class="stat-number"><?= (int)$pendingCount ?></div>
            <div class="small text-muted"><?= __('Waiting for your answer') ?></div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-soft h-100">
            <div class="eyebrow text-uppercase mb-1"><?= __('Sessions today') ?></div>
            <div class="stat-number"><?= count($todayList) ?></div>
            <div class="small text-muted"><?= __('Confirmed for today') ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-soft h-100">
            <div class="eyebrow text-uppercase mb-1"><?= __('This week') ?></div>
            <div class="stat-number"><?= (int)$weekCount ?></div>
            <div class="small text-muted"><?= __('Upcoming confirmed sessions') ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-soft card-calm h-100">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div class="eyebrow text-uppercase"><?= __('High priority') ?></div>
                <span class="chip"><?= __('Coming soon') ?></span>
            </div>
            <p class="small mb-0"><?= __('Students who may need extra care will appear here once assessments are added.') ?></p>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-7">
        <section class="card-soft h-auto" aria-labelledby="attn-title">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h6 fw-semibold mb-0" id="attn-title"><?= __('Needs your attention') ?></h2>
                <span class="chip<?= $attentionCount > 0 ? ' chip-alert' : '' ?>"><?= $attentionCount ?></span>
            </div>

            <?php if ($attentionCount === 0) : ?>
                <p class="small text-muted mb-0"><?= __('Nothing needs your attention right now.') ?></p>
            <?php endif; ?>

            <?php if ($requests) : ?>
                <h3 class="eyebrow text-uppercase mb-2"><?= __('New requests') ?></h3>
                <div class="d-grid gap-2 mb-3">
                    <?php foreach ($requests as $item) :
                        $a = $item['appointment'];
                        $url = $this->Url->build('/counselor/appointments?appt=' . $a->id); ?>
                        <a class="session-row" href="<?= h($url) ?>">
                            <span>
                                <strong class="d-block"><?= h((string)$a->student->name) ?></strong>
                                <span class="small text-muted"><?= h($a->requested_start->i18nFormat('EEE, dd MMM') . ' · ' . $a->requested_start->i18nFormat('h:mm a')) ?> · <?= h($a->modeLabel()) ?></span>
                                <span class="d-flex flex-wrap gap-1 mt-1">
                                    <span class="aging aging-<?= h($item['level']) ?>">
                                        <?php if ($item['level'] === 'late') : ?>
                                            <i class="bi bi-exclamation-circle" aria-hidden="true"></i><?= h(__('Overdue') . ' · ') ?>
                                        <?php elseif ($item['level'] === 'warn') : ?>
                                            <i class="bi bi-clock" aria-hidden="true"></i>
                                        <?php endif; ?>
                                        <?= h(__('Waiting {0}', $span($item['waited']))) ?>
                                    </span>
                                    <span class="aging aging-<?= $item['urgent'] ? 'urgent' : 'new' ?>">
                                        <?php if ($item['urgent']) : ?>
                                            <i class="bi bi-alarm" aria-hidden="true"></i><?= h(__('Urgent') . ' · ') ?>
                                        <?php endif; ?>
                                        <?= h(__('Session in {0}', $span($item['until']))) ?>
                                    </span>
                                </span>
                            </span>
                            <span class="chip"><?= __('Review') ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($studentUpdates) : ?>
                <h3 class="eyebrow text-uppercase mb-2"><?= __('Student updates') ?></h3>
                <div class="d-grid gap-2">
                    <?php foreach ($studentUpdates as $n) : ?>
                        <?= $this->Form->create(null, ['url' => '/notifications/' . $n->id . '/open', 'class' => 'm-0']) ?>
                            <button type="submit" class="session-row w-100 text-start">
                                <span>
                                    <strong class="d-block"><?= h($n->headline()) ?></strong>
                                    <span class="small"><?= h($n->message()) ?></span>
                                </span>
                                <span class="chip"><?= h($updateLabels[$n->type] ?? '') ?></span>
                            </button>
                        <?= $this->Form->end() ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div class="col-12 col-xl-5">
        <div class="d-grid gap-3">
            <section class="card-soft h-auto" aria-labelledby="today-title">
                <h2 class="h6 fw-semibold mb-3" id="today-title"><?= __("Today's sessions") ?></h2>
                <?php if ($todayList) : ?>
                    <div class="d-grid gap-2">
                        <?php foreach ($todayList as $a) : ?>
                            <?= $todayRow($a) ?>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="small text-muted mb-0"><?= __('You have no sessions today.') ?></p>
                <?php endif; ?>
            </section>

            <section class="card-soft card-calm h-auto" aria-labelledby="conf-title">
                <h2 class="h6 fw-semibold" id="conf-title"><?= __('Confidentiality reminder') ?></h2>
                <p class="small mb-0"><?= __('Student information is shared only for care purposes. Every record you open may be logged.') ?></p>
            </section>
        </div>
    </div>
</div>