<?php
/**
 * Booking page: choose a counselor, choose a time, confirm.
 *
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $counselors
 * @var array<int, array<string, list<array{value: string, label: string, end: string}>>> $slots
 * @var \App\Model\Entity\Appointment $appointment
 * @var string|null $error
 * @var int $selectedCounselorId
 * @var string $selectedStart
 */
$this->assign('title', __('Counseling sessions'));
$this->Html->script('booking.js?v=1', ['block' => true]);

$initials = static function (string $name): string {
    $words = preg_split('/\s+/', trim($name)) ?: [];
    $result = '';
    foreach (array_slice($words, 0, 2) as $word) {
        $result .= mb_strtoupper(mb_substr($word, 0, 1));
    }

    return $result;
};

// Collect validation messages to show above the submit button.
$messages = [];
foreach ($appointment->getErrors() as $fieldErrors) {
    foreach ($fieldErrors as $message) {
        $messages[] = $message;
    }
}
if (!empty($error)) {
    $messages[] = $error;
}

$config = [
    'slots' => $slots,
    'selected' => ['counselorId' => $selectedCounselorId, 'start' => $selectedStart],
    'i18n' => [
        'noTimes' => __('No times available on this day.'),
        'pickDate' => __('Select a date to see times.'),
        'timesOn' => __('Times on {0}'),
        'noSlots' => __('This counselor has no free times right now. Please try another counselor.'),
        'ready' => __('Ready to request'),
        'needTime' => __('Select a time'),
        'whereOnline' => __('Online · Private video session'),
        'whereInPerson' => __('In person · {0}', \App\Model\Entity\Appointment::inPersonLocation()),
        'chipOnline' => __('Online session · 50 minutes'),
        'chipInPerson' => __('In-person session · 50 minutes'),
    ],
];
$mode = $appointment->mode === 'in_person' ? 'in_person' : 'online';
$counselorCount = count($counselors instanceof \Cake\Collection\CollectionInterface ? $counselors->toList() : (array)$counselors);
?>

<h1 class="h3 fw-semibold mb-1"><?= __('Make time for yourself') ?></h1>
<p class="text-muted mb-3"><?= __('Choose someone to talk to and a time that fits your day.') ?></p>

<ol class="step-row list-unstyled mb-3" aria-label="<?= __('Booking steps') ?>">
    <li class="step-pill" data-step="1"><span class="step-num">1</span><?= __('Choose counselor') ?></li>
    <li class="step-pill" data-step="2"><span class="step-num">2</span><?= __('Select a time') ?></li>
    <li class="step-pill" data-step="3"><span class="step-num">3</span><?= __('Confirm session') ?></li>
</ol>

<?php if ($counselorCount === 0) : ?>
    <div class="card-soft"><?= __('No counselors are available to book right now. Please check back soon.') ?></div>
<?php else : ?>
<?= $this->Form->create(null, ['url' => '/student/book', 'id' => 'bookingForm', 'novalidate' => true]) ?>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="d-grid gap-3">

            <section class="card-soft h-auto" aria-labelledby="counselor-title">
                <h2 class="h5 fw-semibold" id="counselor-title"><?= __('Choose your counselor') ?></h2>
                <p class="small text-muted"><?= __('Find a comfortable fit. You can choose a different counselor later.') ?></p>
                <div role="radiogroup" aria-labelledby="counselor-title">
                    <?php foreach ($counselors as $i => $counselor) :
                        $isChecked = $selectedCounselorId ? $selectedCounselorId === (int)$counselor->id : $i === 0;
                        $details = implode(' · ', array_filter([(string)$counselor->specialisation, (string)$counselor->languages])); ?>
                        <label class="counselor-option">
                            <input type="radio" class="visually-hidden" name="counselor_id" value="<?= (int)$counselor->id ?>" data-name="<?= h($counselor->name) ?>"<?= $isChecked ? ' checked' : '' ?>>
                            <span class="avatar" aria-hidden="true"><?= h($initials((string)$counselor->name)) ?></span>
                            <span class="flex-grow-1">
                                <strong class="d-block"><?= h($counselor->name) ?></strong>
                                <span class="small text-muted"><?= h($details) ?></span>
                            </span>
                            <i class="bi bi-check-circle-fill option-check" aria-hidden="true"></i>
                        </label>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="card-soft h-auto" aria-labelledby="time-title">
                <h2 class="h5 fw-semibold" id="time-title"><?= __('Find a time to talk') ?></h2>
                <p class="small text-muted"><?= __('All times are in Malaysia Time (MYT, UTC+8).') ?></p>
                <div class="row g-4">
                    <div class="col-md-7">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <button type="button" class="btn-ghost" id="calPrev" aria-label="<?= __('Previous month') ?>"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                            <strong id="calTitle" aria-live="polite"></strong>
                            <button type="button" class="btn-ghost" id="calNext" aria-label="<?= __('Next month') ?>"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                        </div>
                        <div class="cal-grid" id="calGrid" role="group" aria-labelledby="calTitle"></div>
                    </div>
                    <div class="col-md-5">
                        <div class="fw-semibold mb-1" id="slotHeading" aria-live="polite"></div>
                        <span class="chip" id="modeChip"><?= __('Online session · 50 minutes') ?></span>
                        <div class="slot-grid mt-2" id="slotGrid"></div>
                        <p class="small text-muted mt-3 mb-0" id="noteOnline"><?= __('Your video link will appear in your appointment details after your counselor confirms.') ?></p>
                        <p class="small text-muted mt-3 mb-0" id="noteInPerson" hidden><?= __('Your counselor will confirm the room before your session.') ?></p>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <div class="col-lg-4">
        <div class="d-grid gap-3">
            <aside class="card-soft h-auto" aria-labelledby="summary-title">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h2 class="h5 fw-semibold mb-0" id="summary-title"><?= __('Your session summary') ?></h2>
                    <span class="chip" id="summaryStatus"><?= __('Select a time') ?></span>
                </div>

                <dl class="summary-list small mb-3">
                    <dt><?= __('Counselor') ?></dt>
                    <dd id="sumCounselor">-</dd>
                    <dt><?= __('When') ?></dt>
                    <dd id="sumWhen">-</dd>
                    <dt><?= __('Where') ?></dt>
                    <dd id="sumWhere"><?= __('Online · Private video session') ?></dd>
                </dl>

                <input type="hidden" name="requested_start" id="requestedStart" value="<?= h($selectedStart) ?>">

                <fieldset class="mb-3">
                    <legend class="form-label small fw-medium"><?= __('How would you like to meet?') ?></legend>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="mode" id="mode-online" value="online"<?= $mode === 'online' ? ' checked' : '' ?>>
                            <label class="role-card w-100" for="mode-online"><i class="bi bi-camera-video" aria-hidden="true"></i><span><?= __('Online') ?></span></label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="mode" id="mode-in-person" value="in_person"<?= $mode === 'in_person' ? ' checked' : '' ?>>
                            <label class="role-card w-100" for="mode-in-person"><i class="bi bi-geo-alt" aria-hidden="true"></i><span><?= __('In person') ?></span></label>
                        </div>
                    </div>
                </fieldset>

                <label class="form-label small fw-medium" for="message"><?= __("Anything you'd like to share? (optional)") ?></label>
                <textarea class="form-control mb-3" id="message" name="message" rows="3" maxlength="1000" placeholder="<?= h(__("I'd like to talk about sleep and study balance.")) ?>"><?= h((string)$appointment->message) ?></textarea>

                <label class="form-label small fw-medium" for="access_needs"><?= __('Do you need anything to make the session accessible? (optional)') ?></label>
                <textarea class="form-control mb-3" id="access_needs" name="access_needs" rows="2" maxlength="500" placeholder="<?= h(__('For example, a sign language interpreter or live captions.')) ?>"><?= h((string)$appointment->access_needs) ?></textarea>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="consent" name="consent" value="1">
                    <label class="form-check-label small" for="consent"><?= __('I understand the confidentiality information and consent to this session.') ?></label>
                </div>

                <?php if ($messages) : ?>
                    <div class="alert alert-danger small py-2" role="alert">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($messages as $message) : ?>
                                <li><?= h($message) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-me w-100" id="confirmBtn" disabled><i class="bi bi-arrow-right me-2" aria-hidden="true"></i><?= __('Request appointment') ?></button>
            </aside>

            <aside class="card-soft card-hero h-auto small" aria-labelledby="plans-title">
                <strong id="plans-title" class="d-block mb-1"><i class="bi bi-shield-check me-1" aria-hidden="true"></i><?= __('Plans can change') ?></strong>
                <?= __('Your counselor will review your request and confirm it. You can cancel any time from your appointment details. This booking is not an urgent-support service.') ?>
            </aside>
        </div>
    </div>
</div>
<?= $this->Form->end() ?>

<script type="application/json" id="bookingData"><?= json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<?php endif; ?>