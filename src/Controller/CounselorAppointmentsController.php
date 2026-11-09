<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Appointment;
use App\Model\Enum\AppointmentStatus;
use Cake\Http\Exception\NotFoundException;
use Cake\I18n\DateTime;
use DateTimeImmutable;

/**
 * Counselor side of appointments: weekly view, requests, confirm, decline and cancel.
 */
class CounselorAppointmentsController extends CounselorBaseController
{
    /** Statuses that still hold a time slot. */
    private const OPEN_STATUSES = [
        AppointmentStatus::Pending,
        AppointmentStatus::Approved,
        AppointmentStatus::RescheduleProposed,
    ];

    /** How many days ahead free slots are offered when suggesting another time. */
    private const SUGGEST_DAYS_AHEAD = 21;

    /** Statuses shown on the weekly calendar. */
    private const CALENDAR_STATUSES = [
        AppointmentStatus::Pending,
        AppointmentStatus::Approved,
        AppointmentStatus::RescheduleProposed,
        AppointmentStatus::Completed,
        AppointmentStatus::Cancelled, // kept visible (greyed out) so a cancellation is never silent
    ];

    /** Weekly view with the day's sessions and the selected appointment. */
    public function index(): void
    {
        $Appointments = $this->fetchTable('Appointments');
        $me = $this->counselorId();
        $today = new DateTimeImmutable('today');

        // A selected appointment decides which week and day to show.
        $selected = null;
        $selectedId = (int)$this->request->getQuery('appt');
        if ($selectedId > 0) {
            $selected = $this->findOwn($selectedId, false);
        }

        $dayParam = $this->parseDate((string)$this->request->getQuery('day'));
        $weekParam = $this->parseDate((string)$this->request->getQuery('week'));

        if ($selected !== null) {
            $dayParam = new DateTimeImmutable($selected->requested_start->format('Y-m-d'));
            $weekParam = $dayParam;
        }

        $base = $weekParam ?? $dayParam ?? $today;
        if ((int)$base->format('N') >= 6) {
            $base = $base->modify('next monday'); // weekends roll over to the next working week
        }
        $monday = $base->modify('monday this week');

        $weekDays = [];
        for ($i = 0; $i < 5; $i++) {
            $weekDays[] = $monday->modify("+{$i} days");
        }
        $weekKeys = array_map(static fn(DateTimeImmutable $d): string => $d->format('Y-m-d'), $weekDays);

        $day = $dayParam !== null ? $dayParam->format('Y-m-d') : null;
        if ($day === null || !in_array($day, $weekKeys, true)) {
            $day = in_array($today->format('Y-m-d'), $weekKeys, true) ? $today->format('Y-m-d') : $weekKeys[0];
        }

        $statuses = array_map(static fn(AppointmentStatus $s): string => $s->value, self::CALENDAR_STATUSES);
        $weekAppointments = $Appointments->find()
            ->contain(['Students'])
            ->where([
                'Appointments.counselor_id' => $me,
                'Appointments.requested_start >=' => $monday->format('Y-m-d 00:00:00'),
                'Appointments.requested_start <' => $monday->modify('+5 days')->format('Y-m-d 00:00:00'),
                'Appointments.status IN' => $statuses,
            ])
            ->orderBy(['Appointments.requested_start' => 'ASC'])
            ->all();

        $byDay = array_fill_keys($weekKeys, []);
        foreach ($weekAppointments as $appointment) {
            $byDay[$appointment->requested_start->format('Y-m-d')][] = $appointment;
        }
        $dayAppointments = $byDay[$day] ?? [];

        if ($selected === null && $dayAppointments !== []) {
            $selected = $dayAppointments[0];
        }

        // Free times to suggest, only needed while a pending request is open.
        $freeSlots = [];
        if ($selected !== null && $selected->status === AppointmentStatus::Pending && !$selected->requested_start->isPast()) {
            $freeSlots = $this->freeSlots();
        }

        $requests = $Appointments->find()
            ->contain(['Students'])
            ->where([
                'Appointments.counselor_id' => $me,
                'Appointments.status' => AppointmentStatus::Pending->value,
                'Appointments.requested_start >' => DateTime::now(),
            ])
            ->orderBy(['Appointments.requested_start' => 'ASC'])
            ->all();

        $weekLabel = $this->weekLabel($monday, $monday->modify('+4 days'));
        $prevWeek = $monday->modify('-7 days')->format('Y-m-d');
        $nextWeek = $monday->modify('+7 days')->format('Y-m-d');
        $weekKey = $monday->format('Y-m-d');
        $todayKey = $today->format('Y-m-d');

        $this->set(compact(
            'weekDays',
            'byDay',
            'day',
            'dayAppointments',
            'selected',
            'freeSlots',
            'requests',
            'weekLabel',
            'prevWeek',
            'nextWeek',
            'weekKey',
            'todayKey',
        ));
    }

    /** Links from notifications land here and are sent to the weekly view. */
    public function view(string $id)
    {
        $appointment = $this->findOwn((int)$id);

        return $this->redirect($this->pageUrl($appointment));
    }

    /** Confirm a pending request, optionally with a video link and a note. */
    public function confirm(string $id)
    {
        $this->request->allowMethod(['post']);
        $appointment = $this->findOwn((int)$id);

        if ($appointment->status !== AppointmentStatus::Pending) {
            $this->notice('error', __('This request has already been answered.'));

            return $this->redirect($this->pageUrl($appointment));
        }
        if ($appointment->requested_start->isPast()) {
            $this->notice('error', __('This time has already passed. Please decline the request.'));

            return $this->redirect($this->pageUrl($appointment));
        }

        $link = trim((string)$this->request->getData('meeting_link'));
        if ($link !== '' && !$this->isWebLink($link)) {
            $this->notice('error', __('Please enter a valid link that starts with https://'));

            return $this->redirect($this->pageUrl($appointment));
        }
        $note = $this->cleanNote($this->request->getData('decision_note'));

        $appointment->status = AppointmentStatus::Approved;
        $appointment->meeting_link = $link !== '' ? $link : null;
        $appointment->decision_note = $note;
        $appointment->decided_at = DateTime::now();

        if ($this->fetchTable('Appointments')->save($appointment)) {
            $this->notifyStudent('appointment_confirmed', 'Appointment confirmed', $appointment, $note);
            $this->notice('success', __('Appointment confirmed. The student has been notified.'));
        } else {
            $this->notice('error', __('Sorry, something went wrong. Please try again.'));
        }

        return $this->redirect($this->pageUrl($appointment));
    }

    /** Suggest another time from the counselor's own free slots. */
    public function propose(string $id)
    {
        $this->request->allowMethod(['post']);
        $appointment = $this->findOwn((int)$id);

        if ($appointment->status !== AppointmentStatus::Pending) {
            $this->notice('error', __('This request has already been answered.'));

            return $this->redirect($this->pageUrl($appointment));
        }

        $start = $this->parseDateTime((string)$this->request->getData('slot'));
        if ($start === null) {
            $this->notice('error', __('Please choose a new time.'));

            return $this->redirect($this->pageUrl($appointment));
        }

        $link = trim((string)$this->request->getData('meeting_link'));
        if ($link !== '' && !$this->isWebLink($link)) {
            $this->notice('error', __('Please enter a valid link that starts with https://'));

            return $this->redirect($this->pageUrl($appointment));
        }
        $note = $this->cleanNote($this->request->getData('decision_note'));

        $Appointments = $this->fetchTable('Appointments');
        $Availability = $this->fetchTable('CounselorAvailability');
        $me = $this->counselorId();
        $openValues = array_map(static fn(AppointmentStatus $s): string => $s->value, self::OPEN_STATUSES);

        $saved = $Appointments->getConnection()->transactional(
            function () use ($Appointments, $Availability, $appointment, $start, $link, $note, $me, $openValues) {
                // Lock the slot so it cannot be booked or suggested twice at the same moment.
                $slot = $Availability->find()
                    ->where(['counselor_id' => $me, 'start_at' => $start])
                    ->epilog('FOR UPDATE')
                    ->first();
                if ($slot === null || $slot->start_at->isPast()) {
                    return false;
                }

                $taken = $Appointments->find()
                    ->where([
                        'counselor_id' => $me,
                        'requested_start' => $start,
                        'status IN' => $openValues,
                        'id !=' => $appointment->id,
                    ])
                    ->count();
                $held = $Appointments->find()
                    ->where([
                        'counselor_id' => $me,
                        'proposed_start' => $start,
                        'status' => AppointmentStatus::RescheduleProposed->value,
                        'id !=' => $appointment->id,
                    ])
                    ->count();
                if ($taken > 0 || $held > 0) {
                    return false;
                }

                $appointment->status = AppointmentStatus::RescheduleProposed;
                $appointment->proposed_start = $slot->start_at;
                $appointment->meeting_link = $link !== '' ? $link : null;
                $appointment->decision_note = $note;
                $appointment->decided_at = DateTime::now();

                return $Appointments->save($appointment) ?: false;
            },
        );

        if ($saved === false) {
            $this->notice('error', __('Sorry, that time is no longer free. Please choose another time.'));

            return $this->redirect($this->pageUrl($appointment));
        }

        $this->notifyStudent('appointment_rescheduled', 'New time proposed', $appointment, $note, $start->format('Y-m-d H:i:s'));
        $this->notice('success', __('New time suggested. The student has been notified.'));

        return $this->redirect($this->pageUrl($appointment));
    }

    /** Tell the student the requested time is not available. */
    public function decline(string $id)
    {
        $this->request->allowMethod(['post']);
        $appointment = $this->findOwn((int)$id);

        if ($appointment->status !== AppointmentStatus::Pending) {
            $this->notice('error', __('This request has already been answered.'));

            return $this->redirect($this->pageUrl($appointment));
        }

        $note = $this->cleanNote($this->request->getData('decision_note'));
        $appointment->status = AppointmentStatus::Rejected;
        $appointment->decision_note = $note;
        $appointment->decided_at = DateTime::now();

        if ($this->fetchTable('Appointments')->save($appointment)) {
            $this->notifyStudent('appointment_declined', 'Appointment not available', $appointment, $note);
            $this->notice('success', __('The student has been told this time is not available.'));
        } else {
            $this->notice('error', __('Sorry, something went wrong. Please try again.'));
        }

        return $this->redirect($this->pageUrl($appointment));
    }

    /** Cancel a confirmed session. */
    public function cancel(string $id)
    {
        $this->request->allowMethod(['post']);
        $appointment = $this->findOwn((int)$id);

        if ($appointment->status !== AppointmentStatus::Approved) {
            $this->notice('error', __('Only confirmed sessions can be cancelled.'));

            return $this->redirect($this->pageUrl($appointment));
        }

        $note = $this->cleanNote($this->request->getData('decision_note'));
        $appointment->status = AppointmentStatus::Cancelled;
        $appointment->decision_note = $note;

        if ($this->fetchTable('Appointments')->save($appointment)) {
            $this->notifyStudent('appointment_cancelled', 'Appointment cancelled', $appointment, $note);
            $this->notice('success', __('The session was cancelled and the student has been notified.'));
        } else {
            $this->notice('error', __('Sorry, something went wrong. Please try again.'));
        }

        return $this->redirect($this->pageUrl($appointment));
    }

    /** Load one of this counselor's appointments (404 if it belongs to someone else). */
    private function findOwn(int $id, bool $required = true): ?Appointment
    {
        $appointment = $this->fetchTable('Appointments')->find()
            ->contain(['Students'])
            ->where(['Appointments.id' => $id, 'Appointments.counselor_id' => $this->counselorId()])
            ->first();
        if ($appointment === null && $required) {
            throw new NotFoundException();
        }

        return $appointment;
    }

    /** URL of the weekly view focused on one appointment. */
    private function pageUrl(Appointment $appointment): string
    {
        return '/counselor/appointments?' . http_build_query([
            'appt' => $appointment->id,
            'day' => $appointment->requested_start->format('Y-m-d'),
        ]);
    }

    /**
     * This counselor's free slots for the next few weeks, grouped by date.
     *
     * @return array<string, list<array{value: string, label: string}>>
     */
    private function freeSlots(): array
    {
        $me = $this->counselorId();
        $now = DateTime::now();
        $until = $now->modify('+' . self::SUGGEST_DAYS_AHEAD . ' days');
        $openValues = array_map(static fn(AppointmentStatus $s): string => $s->value, self::OPEN_STATUSES);

        $slots = $this->fetchTable('CounselorAvailability')->find()
            ->where(['counselor_id' => $me, 'start_at >' => $now, 'start_at <=' => $until])
            ->orderBy(['start_at' => 'ASC'])
            ->all();

        $taken = [];
        $held = $this->fetchTable('Appointments')->find()
            ->where(['counselor_id' => $me, 'status IN' => $openValues])
            ->all();
        foreach ($held as $appointment) {
            $taken[$appointment->requested_start->format('Y-m-d H:i:s')] = true;
            if ($appointment->status === AppointmentStatus::RescheduleProposed && $appointment->proposed_start !== null) {
                $taken[$appointment->proposed_start->format('Y-m-d H:i:s')] = true;
            }
        }

        $result = [];
        foreach ($slots as $slot) {
            $value = $slot->start_at->format('Y-m-d H:i:s');
            if (isset($taken[$value])) {
                continue;
            }
            $result[$slot->start_at->format('Y-m-d')][] = [
                'value' => $value,
                'label' => $slot->start_at->i18nFormat('h:mm a') . ' - ' . $slot->end_at->i18nFormat('h:mm a'),
            ];
        }

        return $result;
    }

    private function parseDateTime(string $value): ?DateTime
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value);
        if ($date === false || $date->format('Y-m-d H:i:s') !== $value) {
            return null;
        }

        return new DateTime($value);
    }

    private function parseDate(string $value): ?DateTimeImmutable
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value ? $date : null;
    }

    /** True for http(s) links only, so a stored link can never run script. */
    private function isWebLink(string $link): bool
    {
        if (strlen($link) > 255 || filter_var($link, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return in_array(strtolower((string)parse_url($link, PHP_URL_SCHEME)), ['http', 'https'], true);
    }

    private function cleanNote(mixed $value): ?string
    {
        $text = trim((string)$value);

        return $text === '' ? null : mb_substr($text, 0, 500);
    }

    /** "09-13 November 2026" (or "30 Nov - 04 Dec 2026" across months), in the active language. */
    private function weekLabel(DateTimeImmutable $start, DateTimeImmutable $end): string
    {
        $from = new DateTime($start->format('Y-m-d'));
        $to = new DateTime($end->format('Y-m-d'));
        if ($start->format('Y-m') === $end->format('Y-m')) {
            return $from->i18nFormat('dd') . '–' . $to->i18nFormat('dd MMMM y');
        }

        return $from->i18nFormat('dd MMM') . ' – ' . $to->i18nFormat('dd MMM y');
    }
}