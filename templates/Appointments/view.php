<?php
/**
 * One appointment, with a short confirmation banner right after booking.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Appointment $appointment
 * @var bool $justSent
 */
$this->assign('title', __('Counseling sessions'));

$counselor = $appointment->counselor;
$start = $appointment->requested_start;
$end = (clone $start)->modify('+' . (int)$appointment->duration_minutes . ' minutes');
$words = preg_split('/\s+/', trim((string)$counselor->name)) ?: [];
$counselorInitials = '';
foreach (array_slice($words, 0, 2) as $word) {
    $counselorInitials .= mb_strtoupper(mb_substr($word, 0, 1));
}
$dateText = $start->i18nFormat('EEEE, dd MMMM y');
$timeText = $start->i18nFormat('h:mm a') . ' - ' . $end->i18nFormat('h:mm a') . ' MYT';
?>

<h1 class="h3 fw-semibold mb-1">
    <?= $justSent ? __("You've made space for support.") : __('Your appointment') ?>
</h1>
<p class="text-muted mb-3">
    <?= $justSent
        ? __('Your request has been sent. You will see an update here when your counselor responds.')
        : __('Details of your counseling session.') ?>
</p>

<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">
        <?php if ($this->getRequest()->getQuery('proposal') === 'unavailable') : ?>
            <div class="alert alert-warning py-2 small" role="alert"><?= __('Sorry, that new time is no longer available. Please book another time.') ?></div>
        <?php endif; ?>

        <?php if ($appointment->status === \App\Model\Enum\AppointmentStatus::RescheduleProposed && $appointment->proposed_start) :
            $pStart = $appointment->proposed_start;
            $pEnd = (clone $pStart)->modify('+' . (int)$appointment->duration_minutes . ' minutes'); ?>
            <section class="card-soft card-hero h-auto mb-3" aria-labelledby="proposal-title">
                <div class="eyebrow text-uppercase mb-1"><?= __('Your counselor suggested a new time') ?></div>
                <h2 class="h5 fw-semibold" id="proposal-title"><?= h($pStart->i18nFormat('EEEE, dd MMMM y')) ?></h2>
                <p class="mb-3"><?= h($pStart->i18nFormat('h:mm a') . ' - ' . $pEnd->i18nFormat('h:mm a') . ' MYT') ?> · <?= h($appointment->modeLabel()) ?></p>
                <div class="d-flex flex-wrap gap-2">
                    <?= $this->Form->postLink(
                        __('Accept new time'),
                        '/student/sessions/' . $appointment->id . '/accept',
                        ['class' => 'btn btn-me px-4'],
                    ) ?>
                    <?= $this->Form->postLink(
                        __('Decline'),
                        '/student/sessions/' . $appointment->id . '/decline',
                        ['class' => 'btn btn-outline-secondary', 'confirm' => __('Decline this new time?')],
                    ) ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="card-soft h-auto text-center mb-3" aria-labelledby="appt-title">
            <span class="<?= h($appointment->status->chipClass()) ?>"><?= h($appointment->status->label()) ?></span>
            <h2 class="h4 fw-semibold my-3" id="appt-title"><?= h($dateText) ?></h2>

            <div class="card-hero rounded-4 p-3 text-start d-flex flex-wrap gap-3 align-items-center mb-3">
                <span class="avatar" aria-hidden="true"><?= h($counselorInitials) ?></span>
                <div class="flex-grow-1">
                    <div class="fw-semibold"><?= h($counselor->name) ?></div>
                    <div class="small text-muted"><?= h((string)$counselor->specialisation) ?></div>
                </div>
                <div class="small">
                    <div><?= h($timeText) ?></div>
                    <div class="text-muted"><?= h($appointment->modeLabel()) ?></div>
                    <div class="text-muted"><?= h(__('Booking reference · {0}', $appointment->reference)) ?></div>
                </div>
            </div>

            <?php if ($appointment->decision_note) : ?>
                <div class="text-start small mb-3">
                    <strong><?= __('Message from your counselor') ?></strong>
                    <p class="mb-0"><?= h($appointment->decision_note) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($appointment->status === \App\Model\Enum\AppointmentStatus::Approved && $appointment->mode === 'online' && $appointment->meeting_link) : ?>
                <div class="mb-3">
                    <a class="btn btn-me px-4" href="<?= h($appointment->meeting_link) ?>" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-camera-video me-2" aria-hidden="true"></i><?= __('Join video session') ?>
                    </a>
                </div>
            <?php endif; ?>

            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="<?= $this->Url->build('/student/sessions') ?>" class="btn btn-me px-4"><?= __('All my sessions') ?></a>
                <?php if ($appointment->status->isOpen()) : ?>
                    <?= $this->Form->postLink(
                        __('Cancel appointment'),
                        '/student/sessions/' . $appointment->id . '/cancel',
                        ['class' => 'btn btn-outline-secondary', 'confirm' => __('Cancel this appointment?')],
                    ) ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="card-soft card-calm h-auto" aria-labelledby="before-title">
            <h2 class="h6 fw-semibold" id="before-title"><?= __('Before your session') ?></h2>
            <p class="small mb-0">
                <?php if ($appointment->mode === 'in_person') : ?>
                    <?= __('Please arrive a few minutes early. Your counselor will confirm the room before your session. You are welcome to bring a few notes, or simply come as you are. Your counselor will explain confidentiality and its limits at the start.') ?>
                <?php else : ?>
                    <?= __('Find a quiet, private place and check your internet connection. You are welcome to bring a few notes, or simply come as you are. Your counselor will explain confidentiality and its limits at the start.') ?>
                <?php endif; ?>
            </p>
        </section>
    </div>
</div>