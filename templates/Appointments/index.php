<?php
/**
 * The student's appointments.
 *
 * @var \App\View\AppView $this
 * @var list<\App\Model\Entity\Appointment> $upcoming
 * @var list<\App\Model\Entity\Appointment> $past
 */
$this->assign('title', __('Counseling sessions'));

$row = function (\App\Model\Entity\Appointment $appointment): string {
    $start = $appointment->requested_start;
    $title = h($start->i18nFormat('EEE, dd MMM y') . ' · ' . $start->i18nFormat('h:mm a'));
    $name = h((string)$appointment->counselor->name);
    $chip = '<span class="' . h($appointment->status->chipClass()) . '">' . h($appointment->status->label()) . '</span>';
    $url = h('/student/sessions/' . $appointment->id);

    return '<a class="session-row" href="' . $url . '"><span><strong class="d-block">' . $title
        . '</strong><span class="small text-muted">' . $name . '</span></span>' . $chip . '</a>';
};
?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
    <div>
        <h1 class="h3 fw-semibold mb-1"><?= __('Counseling sessions') ?></h1>
        <p class="text-muted small mb-0"><?= __('Your requests and upcoming sessions in one place.') ?></p>
    </div>
    <a href="<?= $this->Url->build('/student/book') ?>" class="btn btn-me px-4"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i><?= __('Book a session') ?></a>
</div>

<section class="card-soft h-auto mb-3" aria-labelledby="upcoming-title">
    <h2 class="h6 fw-semibold mb-3" id="upcoming-title"><?= __('Upcoming and pending') ?></h2>
    <?php if ($upcoming) : ?>
        <div class="d-grid gap-2">
            <?php foreach ($upcoming as $appointment) : ?>
                <?= $row($appointment) ?>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p class="small text-muted mb-0"><?= __('You have no upcoming sessions.') ?></p>
    <?php endif; ?>
</section>

<?php if ($past) : ?>
    <section class="card-soft h-auto" aria-labelledby="past-title">
        <h2 class="h6 fw-semibold mb-3" id="past-title"><?= __('Past and closed') ?></h2>
        <div class="d-grid gap-2">
            <?php foreach ($past as $appointment) : ?>
                <?= $row($appointment) ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>