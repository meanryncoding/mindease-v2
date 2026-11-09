<?php
/**
 * Counselor appointments: requests, weekly strip, day list and the selected appointment.
 *
 * @var \App\View\AppView $this
 * @var list<\DateTimeImmutable> $weekDays
 * @var array<string, list<\App\Model\Entity\Appointment>> $byDay
 * @var string $day
 * @var list<\App\Model\Entity\Appointment> $dayAppointments
 * @var \App\Model\Entity\Appointment|null $selected
 * @var array<string, list<array{value: string, label: string}>> $freeSlots
 * @var iterable<\App\Model\Entity\Appointment> $requests
 * @var string $weekLabel
 * @var string $prevWeek
 * @var string $nextWeek
 * @var string $weekKey
 * @var string $todayKey
 */

use App\Model\Enum\AppointmentStatus;
use Cake\I18n\DateTime;

$this->assign('title', __('Appointments'));

$requestList = is_array($requests) ? $requests : iterator_to_array($requests, false);
$apptUrl = fn(int $id): string => $this->Url->build('/counselor/appointments?appt=' . $id);
$dayUrl = fn(string $date): string => $this->Url->build('/counselor/appointments?' . http_build_query(['week' => $weekKey, 'day' => $date]));
$weekUrl = fn(string $date): string => $this->Url->build('/counselor/appointments?' . http_build_query(['week' => $date]));
$timeRange = static function (\App\Model\Entity\Appointment $a): string {
    $end = (clone $a->requested_start)->modify('+' . (int)$a->duration_minutes . ' minutes');

    return $a->requested_start->i18nFormat('h:mm a') . ' - ' . $end->i18nFormat('h:mm a');
};
$dayTitle = (new DateTime($day))->i18nFormat('EEEE, dd MMMM y');
?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
    <div>
        <h1 class="h3 fw-semibold mb-1"><?= __('Appointments') ?></h1>
        <p class="text-muted small mb-0"><?= __('Answer new requests and see your week at a glance.') ?></p>
    </div>
    <nav class="d-flex align-items-center gap-2" aria-label="<?= __('Week navigation') ?>">
        <a class="btn btn-outline-secondary btn-sm" href="<?= h($weekUrl($prevWeek)) ?>" aria-label="<?= __('Previous week') ?>"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
        <span class="fw-semibold small px-1"><?= h($weekLabel) ?></span>
        <a class="btn btn-outline-secondary btn-sm" href="<?= h($weekUrl($nextWeek)) ?>" aria-label="<?= __('Next week') ?>"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
        <a class="btn btn-outline-secondary btn-sm" href="<?= h($this->Url->build('/counselor/appointments')) ?>"><?= __('Today') ?></a>
    </nav>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-7">

        <section class="card-soft h-auto mb-3" aria-labelledby="req-title">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h6 fw-semibold mb-0" id="req-title"><?= __('New requests') ?></h2>
                <span class="chip<?= $requestList ? ' chip-alert' : '' ?>"><?= count($requestList) ?></span>
            </div>
            <?php if ($requestList) : ?>
                <div class="d-grid gap-2">
                    <?php foreach ($requestList as $a) : ?>
                        <a class="session-row<?= $selected && $selected->id === $a->id ? ' is-selected' : '' ?>" href="<?= h($apptUrl($a->id)) ?>">
                            <span>
                                <strong class="d-block"><?= h((string)$a->student->name) ?></strong>
                                <span class="small text-muted"><?= h($a->requested_start->i18nFormat('EEE, dd MMM') . ' · ' . $a->requested_start->i18nFormat('h:mm a')) ?> · <?= h($a->modeLabel()) ?></span>
                            </span>
                            <span class="chip"><?= __('Review') ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="small text-muted mb-0"><?= __('No new requests right now.') ?></p>
            <?php endif; ?>
        </section>

        <section class="card-soft h-auto" aria-labelledby="week-title">
            <h2 class="h6 fw-semibold mb-3" id="week-title"><?= __('This week') ?></h2>

            <div class="day-strip mb-3">
                <?php foreach ($weekDays as $d) :
                    $key = $d->format('Y-m-d');
                    $list = array_values(array_filter(
                        $byDay[$key] ?? [],
                        static fn($a) => $a->status !== AppointmentStatus::Cancelled,
                    ));
                    $pending = count(array_filter($list, static fn($a) => $a->status === AppointmentStatus::Pending));
                    $label = new DateTime($key); ?>
                    <a class="day-card<?= $key === $day ? ' is-selected' : '' ?><?= $key === $todayKey ? ' is-today' : '' ?>"
                       href="<?= h($dayUrl($key)) ?>"<?= $key === $day ? ' aria-current="date"' : '' ?>
                       aria-label="<?= h($label->i18nFormat('EEEE, dd MMMM') . ': ' . __('{0} sessions', count($list))) ?>">
                        <span class="day-name"><?= h($label->i18nFormat('EEE')) ?></span>
                        <span class="day-num"><?= h($label->i18nFormat('dd')) ?></span>
                        <span class="day-count"><?= count($list) ?><?= $pending ? ' · <i class="bi bi-circle-fill day-dot" aria-hidden="true"></i>' : '' ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <h3 class="h6 fw-semibold"><?= h($dayTitle) ?></h3>
            <?php if ($dayAppointments) : ?>
                <div class="d-grid gap-2">
                    <?php foreach ($dayAppointments as $a) : ?>
                        <a class="appt-row<?= $selected && $selected->id === $a->id ? ' is-selected' : '' ?><?= $a->status === AppointmentStatus::Cancelled ? ' is-cancelled' : '' ?>" href="<?= h($apptUrl($a->id)) ?>">
                            <span class="appt-time"><?= h($a->requested_start->i18nFormat('h:mm a')) ?></span>
                            <span class="flex-grow-1">
                                <strong class="d-block"><?= h((string)$a->student->name) ?></strong>
                                <span class="small text-muted"><?= h($a->modeLabel()) ?></span>
                            </span>
                            <span class="<?= h($a->status->chipClass()) ?>"><?= h($a->status->label()) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="small text-muted mb-0"><?= __('No sessions on this day.') ?></p>
            <?php endif; ?>
        </section>
    </div>

    <div class="col-12 col-xl-5">
        <?php if ($selected === null) : ?>
            <section class="card-soft card-calm h-auto">
                <h2 class="h6 fw-semibold"><?= __('Appointment details') ?></h2>
                <p class="small mb-0"><?= __('Select a request or a session to see the details here.') ?></p>
            </section>
        <?php else :
            $a = $selected;
            $isPending = $a->status === AppointmentStatus::Pending;
            $isPast = $a->requested_start->isPast();
            $isOnline = $a->mode === 'online';
            $base = '/counselor/appointments/' . $a->id; ?>
            <section class="card-soft h-auto" aria-labelledby="detail-title">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div>
                        <div class="eyebrow text-uppercase"><?= __('Appointment details') ?></div>
                        <h2 class="h5 fw-semibold mb-0" id="detail-title"><?= h((string)$a->student->name) ?></h2>
                    </div>
                    <span class="<?= h($a->status->chipClass()) ?>"><?= h($a->status->label()) ?></span>
                </div>

                <dl class="summary-list mb-3">
                    <dt><?= __('When') ?></dt>
                    <dd><?= h($a->requested_start->i18nFormat('EEEE, dd MMMM y')) ?><br><?= h($timeRange($a)) ?> MYT</dd>
                    <dt><?= __('Format') ?></dt>
                    <dd><?= h($a->modeLabel()) ?></dd>
                    <?php if ($a->status === AppointmentStatus::RescheduleProposed && $a->proposed_start) : ?>
                        <dt><?= __('Proposed new time') ?></dt>
                        <dd><?= h($a->proposed_start->i18nFormat('EEEE, dd MMMM y · h:mm a')) ?></dd>
                    <?php endif; ?>
                    <dt><?= __('Reference') ?></dt>
                    <dd><?= h($a->reference) ?></dd>
                    <dt><?= __('Message from the student') ?></dt>
                    <dd><?= $a->message ? nl2br(h($a->message)) : '<span class="text-muted">' . h(__('No message.')) . '</span>' ?></dd>
                    <?php if ($a->access_needs) : ?>
                        <dt><?= __('Access needs') ?></dt>
                        <dd><?= nl2br(h($a->access_needs)) ?></dd>
                    <?php endif; ?>
                    <?php if ($a->status === AppointmentStatus::Approved && $isOnline && $a->meeting_link) : ?>
                        <dt><?= __('Video link') ?></dt>
                        <dd><a href="<?= h($a->meeting_link) ?>" target="_blank" rel="noopener noreferrer"><?= h($a->meeting_link) ?></a></dd>
                    <?php endif; ?>
                    <?php if ($a->decision_note) : ?>
                        <dt><?= __('Your note to the student') ?></dt>
                        <dd><?= nl2br(h($a->decision_note)) ?></dd>
                    <?php endif; ?>
                </dl>

                <?php if ($a->status === AppointmentStatus::Cancelled) : ?>
                    <div class="alert alert-secondary py-2 small" role="status"><?= __('This appointment was cancelled. The time is free again for other students.') ?></div>
                <?php endif; ?>

                <?php if ($a->status === AppointmentStatus::RescheduleProposed) : ?>
                    <div class="alert alert-info py-2 small" role="status"><?= __('Waiting for the student to accept or decline the new time.') ?></div>
                <?php endif; ?>

                <?php if ($isPending) : ?>
                    <?php if ($isPast) : ?>
                        <div class="alert alert-warning py-2 small" role="status"><?= __('This time has already passed. You can only decline this request.') ?></div>
                    <?php else : ?>
                        <?= $this->Form->create(null, ['url' => $base . '/confirm', 'class' => 'mb-3']) ?>
                            <h3 class="h6 fw-semibold"><?= __('Confirm this session') ?></h3>
                            <?php if ($isOnline) : ?>
                                <label class="form-label small fw-medium" for="meeting_link"><?= __('Video link (optional)') ?></label>
                                <input type="url" class="form-control mb-2" id="meeting_link" name="meeting_link" maxlength="255" placeholder="https://">
                            <?php endif; ?>
                            <label class="form-label small fw-medium" for="confirm_note">
                                <?= $isOnline ? __('Note to the student (optional)') : __('Room and note to the student (optional)') ?>
                            </label>
                            <textarea class="form-control mb-2" id="confirm_note" name="decision_note" rows="2" maxlength="500"></textarea>
                            <button type="submit" class="btn btn-me w-100"><i class="bi bi-check2-circle me-2" aria-hidden="true"></i><?= __('Confirm appointment') ?></button>
                        <?= $this->Form->end() ?>
                    <?php endif; ?>

                    <?php if (!$isPast) : ?>
                        <details class="action-details mb-2">
                            <summary><?= __('Suggest another time') ?></summary>
                            <?php if ($freeSlots) : ?>
                                <?= $this->Form->create(null, ['url' => $base . '/propose', 'class' => 'mt-2']) ?>
                                    <label class="form-label small fw-medium" for="propose_slot"><?= __('New time') ?></label>
                                    <select class="form-select mb-2" id="propose_slot" name="slot" required>
                                        <option value=""><?= __('Choose a time') ?></option>
                                        <?php foreach ($freeSlots as $date => $times) : ?>
                                            <optgroup label="<?= h((new DateTime($date))->i18nFormat('EEEE, dd MMM')) ?>">
                                                <?php foreach ($times as $time) : ?>
                                                    <option value="<?= h($time['value']) ?>"><?= h($time['label']) ?></option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($isOnline) : ?>
                                        <label class="form-label small fw-medium" for="propose_link"><?= __('Video link (optional)') ?></label>
                                        <input type="url" class="form-control mb-2" id="propose_link" name="meeting_link" maxlength="255" placeholder="https://">
                                    <?php endif; ?>
                                    <label class="form-label small fw-medium" for="propose_note"><?= __('Note to the student (optional)') ?></label>
                                    <textarea class="form-control mb-2" id="propose_note" name="decision_note" rows="2" maxlength="500"></textarea>
                                    <button type="submit" class="btn btn-outline-secondary w-100"><?= __('Send new time') ?></button>
                                <?= $this->Form->end() ?>
                            <?php else : ?>
                                <p class="small text-muted mt-2 mb-0"><?= __('You have no free slots in the next 3 weeks.') ?></p>
                            <?php endif; ?>
                        </details>
                    <?php endif; ?>

                    <details class="action-details">
                        <summary><?= __('Not available at this time') ?></summary>
                        <?= $this->Form->create(null, ['url' => $base . '/decline', 'class' => 'mt-2']) ?>
                            <label class="form-label small fw-medium" for="decline_note"><?= __('Note to the student (optional)') ?></label>
                            <textarea class="form-control mb-2" id="decline_note" name="decision_note" rows="2" maxlength="500"></textarea>
                            <button type="submit" class="btn btn-outline-secondary w-100"><?= __('Send: not available') ?></button>
                        <?= $this->Form->end() ?>
                    </details>
                <?php elseif ($a->status === AppointmentStatus::Approved && !$isPast) : ?>
                    <details class="action-details">
                        <summary><?= __('Cancel this session') ?></summary>
                        <?= $this->Form->create(null, ['url' => $base . '/cancel', 'class' => 'mt-2']) ?>
                            <label class="form-label small fw-medium" for="cancel_note"><?= __('Reason for the student (optional)') ?></label>
                            <textarea class="form-control mb-2" id="cancel_note" name="decision_note" rows="2" maxlength="500"></textarea>
                            <button type="submit" class="btn btn-outline-secondary w-100"><?= __('Cancel session') ?></button>
                        <?= $this->Form->end() ?>
                    </details>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </div>
</div>