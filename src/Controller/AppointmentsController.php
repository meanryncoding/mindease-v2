<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Appointment;
use App\Model\Enum\AppointmentStatus;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\I18n\DateTime;

/**
 * Student side of counseling appointments: book, list, view, cancel and answer a proposed new time.
 */
class AppointmentsController extends AppController
{
    /** Statuses that still hold a time slot. */
    private const OPEN_STATUSES = [
        AppointmentStatus::Pending,
        AppointmentStatus::Approved,
        AppointmentStatus::RescheduleProposed,
    ];

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $identity = $this->Authentication->getIdentity();
        if ($identity === null || $identity->get('role') !== 'student') {
            return $this->redirect('/');
        }
        $this->viewBuilder()->setLayout('student');

        return null;
    }

    /** List the signed-in student's appointments. */
    public function index(): void
    {
        $appointments = $this->fetchTable('Appointments')->find()
            ->contain(['Counselors'])
            ->where(['Appointments.student_id' => $this->studentId()])
            ->orderBy(['Appointments.requested_start' => 'DESC'])
            ->all();

        $upcoming = [];
        $past = [];
        foreach ($appointments as $appointment) {
            if ($appointment->status->isOpen()) {
                $upcoming[] = $appointment;
            } else {
                $past[] = $appointment;
            }
        }
        // Soonest first for upcoming sessions.
        $upcoming = array_reverse($upcoming);

        $this->set(compact('upcoming', 'past'));
    }

    /** Show one appointment. */
    public function view(string $id): void
    {
        $appointment = $this->findOwn((int)$id);
        $this->set(compact('appointment'));
        $this->set('justSent', $this->request->getQuery('sent') === '1');
    }

    /** Cancel an appointment that is still open. */
    public function cancel(string $id)
    {
        $this->request->allowMethod(['post']);

        $Appointments = $this->fetchTable('Appointments');
        $appointment = $this->findOwn((int)$id);

        if ($appointment->status->isOpen()) {
            $appointment->status = AppointmentStatus::Cancelled;
            $appointment->proposed_start = null;
            if ($Appointments->save($appointment)) {
                $this->notify(
                    $appointment->counselor_id,
                    'appointment_cancelled',
                    'Appointment cancelled',
                    $appointment,
                );
            }
        }

        return $this->redirect('/student/sessions/' . $appointment->id);
    }

    /** Accept the new time the counselor suggested. */
    public function acceptProposal(string $id)
    {
        $this->request->allowMethod(['post']);

        $Appointments = $this->fetchTable('Appointments');
        $Availability = $this->fetchTable('CounselorAvailability');
        $appointment = $this->findOwn((int)$id);
        $back = '/student/sessions/' . $appointment->id;

        if ($appointment->status !== AppointmentStatus::RescheduleProposed || $appointment->proposed_start === null) {
            return $this->redirect($back);
        }
        $start = $appointment->proposed_start;
        if ($start->isPast()) {
            return $this->redirect($back . '?proposal=unavailable');
        }
        $openValues = array_map(static fn(AppointmentStatus $s): string => $s->value, self::OPEN_STATUSES);

        $saved = $Appointments->getConnection()->transactional(
            function () use ($Appointments, $Availability, $appointment, $start, $openValues) {
                // Lock the slot so nobody else can take it while we confirm.
                $slot = $Availability->find()
                    ->where(['counselor_id' => $appointment->counselor_id, 'start_at' => $start])
                    ->epilog('FOR UPDATE')
                    ->first();
                if ($slot === null) {
                    return false;
                }

                $taken = $Appointments->find()
                    ->where([
                        'counselor_id' => $appointment->counselor_id,
                        'requested_start' => $start,
                        'status IN' => $openValues,
                        'id !=' => $appointment->id,
                    ])
                    ->count();
                if ($taken > 0) {
                    return false;
                }

                $appointment->requested_start = $start;
                $appointment->proposed_start = null;
                $appointment->status = AppointmentStatus::Approved;
                $appointment->decided_at = DateTime::now();
                $appointment->duration_minutes = max(
                    1,
                    (int)(($slot->end_at->getTimestamp() - $slot->start_at->getTimestamp()) / 60),
                );

                return $Appointments->save($appointment) ?: false;
            },
        );

        if ($saved === false) {
            return $this->redirect($back . '?proposal=unavailable');
        }

        $this->notify($appointment->counselor_id, 'proposal_accepted', 'New time accepted', $appointment);

        return $this->redirect($back);
    }

    /** Decline the new time the counselor suggested. */
    public function declineProposal(string $id)
    {
        $this->request->allowMethod(['post']);

        $appointment = $this->findOwn((int)$id);
        if ($appointment->status === AppointmentStatus::RescheduleProposed) {
            $proposed = $appointment->proposed_start?->format('Y-m-d H:i:s');
            $appointment->status = AppointmentStatus::Declined;
            if ($this->fetchTable('Appointments')->save($appointment)) {
                $this->notify($appointment->counselor_id, 'proposal_declined', 'New time declined', $appointment, $proposed);
            }
        }

        return $this->redirect('/student/sessions/' . $appointment->id);
    }

    /** Booking page: choose a counselor, a time, and confirm. */
    public function book()
    {
        $Users = $this->fetchTable('Users');
        $Appointments = $this->fetchTable('Appointments');
        $appointment = $Appointments->newEmptyEntity();
        $error = null;

        if ($this->request->is('post')) {
            $result = $this->saveBooking($this->request->getData());
            if ($result instanceof Appointment) {
                return $this->redirect('/student/sessions/' . $result->id . '?sent=1');
            }
            $appointment = $result['entity'];
            $error = $result['error'];
        }

        $counselors = $Users->find()
            ->where(['role' => 'counselor', 'status' => 'active'])
            ->orderBy(['name' => 'ASC'])
            ->all();

        $slots = $this->availableSlots($counselors->extract('id')->toList());

        $selectedCounselorId = (int)$this->request->getData('counselor_id');
        $selectedStart = (string)$this->request->getData('requested_start');

        $this->set(compact('counselors', 'slots', 'appointment', 'error', 'selectedCounselorId', 'selectedStart'));
    }

    /**
     * Validate and store a booking request.
     *
     * @param array<string, mixed> $data Submitted form data.
     * @return \App\Model\Entity\Appointment|array{entity: \App\Model\Entity\Appointment, error: string|null}
     */
    private function saveBooking(array $data): Appointment|array
    {
        $Appointments = $this->fetchTable('Appointments');
        $Availability = $this->fetchTable('CounselorAvailability');

        $counselorId = (int)($data['counselor_id'] ?? 0);
        $appointment = $Appointments->newEntity($data);
        $unavailable = __('Sorry, that time is no longer available. Please choose another time.');

        if ($appointment->getErrors()) {
            return ['entity' => $appointment, 'error' => null];
        }

        $counselor = $this->fetchTable('Users')->find()
            ->where(['id' => $counselorId, 'role' => 'counselor', 'status' => 'active'])
            ->first();
        if ($counselor === null) {
            return ['entity' => $appointment, 'error' => __('Please choose a counselor.')];
        }

        $student = $this->fetchTable('Users')->get($this->studentId());
        $start = $appointment->requested_start;
        $openValues = array_map(static fn(AppointmentStatus $s): string => $s->value, self::OPEN_STATUSES);

        $saved = $Appointments->getConnection()->transactional(
            function () use ($Appointments, $Availability, $appointment, $counselor, $student, $start, $openValues) {
                // Lock the slot row so two students cannot take the same slot at the same moment.
                $slot = $Availability->find()
                    ->where(['counselor_id' => $counselor->id, 'start_at' => $start])
                    ->epilog('FOR UPDATE')
                    ->first();
                if ($slot === null) {
                    return false;
                }

                $taken = $Appointments->find()
                    ->where([
                        'counselor_id' => $counselor->id,
                        'requested_start' => $start,
                        'status IN' => $openValues,
                    ])
                    ->count();
                if ($taken > 0) {
                    return false;
                }

                $held = $Appointments->find()
                    ->where([
                        'counselor_id' => $counselor->id,
                        'proposed_start' => $start,
                        'status' => AppointmentStatus::RescheduleProposed->value,
                    ])
                    ->count();
                if ($held > 0) {
                    return false;
                }

                $appointment->student_id = (int)$student->id;
                $appointment->counselor_id = (int)$counselor->id;
                $appointment->duration_minutes = max(
                    1,
                    (int)(($slot->end_at->getTimestamp() - $slot->start_at->getTimestamp()) / 60),
                );
                $appointment->status = AppointmentStatus::Pending;
                $appointment->consent_at = DateTime::now();
                $appointment->reference = $this->makeReference($start, (string)$student->name);

                return $Appointments->save($appointment) ?: false;
            },
        );

        if ($saved === false) {
            return ['entity' => $appointment, 'error' => $unavailable];
        }

        $this->notify($counselor->id, 'appointment_requested', 'New appointment request', $appointment);

        return $appointment;
    }

    /**
     * Upcoming free slots per counselor, grouped by date.
     *
     * @param list<int> $counselorIds Counselors to load slots for.
     * @return array<int, array<string, list<array{value: string, label: string, end: string}>>>
     */
    private function availableSlots(array $counselorIds): array
    {
        if ($counselorIds === []) {
            return [];
        }

        $now = DateTime::now();
        $slots = $this->fetchTable('CounselorAvailability')->find()
            ->where(['counselor_id IN' => $counselorIds, 'start_at >' => $now])
            ->orderBy(['start_at' => 'ASC'])
            ->all();

        $openValues = array_map(static fn(AppointmentStatus $s): string => $s->value, self::OPEN_STATUSES);
        $taken = [];
        $held = $this->fetchTable('Appointments')->find()
            ->where(['counselor_id IN' => $counselorIds, 'requested_start >' => $now, 'status IN' => $openValues])
            ->all();
        foreach ($held as $appointment) {
            $taken[$appointment->counselor_id . '|' . $appointment->requested_start->format('Y-m-d H:i:s')] = true;
        }
        // A time the counselor has suggested to another student is on hold too.
        $proposals = $this->fetchTable('Appointments')->find()
            ->where([
                'counselor_id IN' => $counselorIds,
                'proposed_start >' => $now,
                'status' => AppointmentStatus::RescheduleProposed->value,
            ])
            ->all();
        foreach ($proposals as $appointment) {
            $taken[$appointment->counselor_id . '|' . $appointment->proposed_start->format('Y-m-d H:i:s')] = true;
        }

        $result = [];
        foreach ($slots as $slot) {
            $value = $slot->start_at->format('Y-m-d H:i:s');
            if (isset($taken[$slot->counselor_id . '|' . $value])) {
                continue;
            }
            $result[$slot->counselor_id][$slot->start_at->format('Y-m-d')][] = [
                'value' => $value,
                'label' => $slot->start_at->i18nFormat('h:mm a'),
                'end' => $slot->end_at->i18nFormat('h:mm a'),
            ];
        }

        return $result;
    }

    /** Booking reference such as ME-261103-AR (session date + student initials). */
    private function makeReference(DateTime $start, string $name): string
    {
        $words = preg_split('/\s+/', trim($name)) ?: [];
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }
        $base = 'ME-' . $start->format('ymd') . '-' . ($initials !== '' ? $initials : 'ST');

        $Appointments = $this->fetchTable('Appointments');
        $reference = $base;
        for ($i = 2; $Appointments->exists(['reference' => $reference]); $i++) {
            $reference = $base . '-' . $i;
        }

        return $reference;
    }

    /**
     * Store an in-app notification. The text is saved as an English message key
     * and the details as JSON, so it can be shown in the reader's language later.
     */
    private function notify(int $userId, string $type, string $title, Appointment $appointment, ?string $when = null): void
    {
        $Notifications = $this->fetchTable('Notifications');
        $student = $this->fetchTable('Users')->get($appointment->student_id);
        $notification = $Notifications->newEntity([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => json_encode([
                'student' => $student->name,
                'when' => $when ?? $appointment->requested_start->format('Y-m-d H:i:s'),
                'mode' => $appointment->mode,
                'reference' => $appointment->reference,
            ], JSON_UNESCAPED_UNICODE),
            'link' => '/counselor/appointments/' . $appointment->id,
        ]);
        $Notifications->save($notification);
    }

    /** The signed-in student's user id. */
    private function studentId(): int
    {
        return (int)$this->Authentication->getIdentity()->getIdentifier();
    }

    /** Load an appointment that belongs to the signed-in student, or show a 404. */
    private function findOwn(int $id): Appointment
    {
        $appointment = $this->fetchTable('Appointments')->find()
            ->contain(['Counselors'])
            ->where(['Appointments.id' => $id, 'Appointments.student_id' => $this->studentId()])
            ->first();
        if ($appointment === null) {
            throw new NotFoundException();
        }

        return $appointment;
    }
}