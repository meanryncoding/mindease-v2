<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Enum\AppointmentStatus;
use Cake\I18n\DateTime;

/**
 * Counselor home ("Care overview").
 */
class CounselorController extends CounselorBaseController
{
    /** A request waiting longer than this (hours) is shown as "waiting" (yellow). */
    private const WAITING_AFTER_HOURS = 24;

    /** A request waiting longer than this (hours) is shown as "overdue" (red). */
    private const OVERDUE_AFTER_HOURS = 48;

    /** A request whose session starts within this many hours is shown as "urgent". */
    private const URGENT_SESSION_WITHIN_HOURS = 24;

    public function overview(): void
    {
        $Appointments = $this->fetchTable('Appointments');
        $me = $this->counselorId();
        $now = DateTime::now();
        $nowTs = $now->getTimestamp();

        $todayStart = date('Y-m-d 00:00:00');
        $tomorrowStart = date('Y-m-d 00:00:00', strtotime('tomorrow'));
        $nextWeekStart = date('Y-m-d 00:00:00', strtotime('monday next week'));

        $mine = static fn($query) => $query->where(['Appointments.counselor_id' => $me]);

        $todaySessions = $mine($Appointments->find())
            ->contain(['Students'])
            ->where([
                'Appointments.status' => AppointmentStatus::Approved->value,
                'Appointments.requested_start >=' => $todayStart,
                'Appointments.requested_start <' => $tomorrowStart,
            ])
            ->orderBy(['Appointments.requested_start' => 'ASC'])
            ->all();

        $weekCount = $mine($Appointments->find())
            ->where([
                'Appointments.status' => AppointmentStatus::Approved->value,
                'Appointments.requested_start >' => $now,
                'Appointments.requested_start <' => $nextWeekStart,
            ])
            ->count();

        // Pending requests, each with how long it has waited and how soon the session is.
        $pending = $mine($Appointments->find())
            ->contain(['Students'])
            ->where([
                'Appointments.status' => AppointmentStatus::Pending->value,
                'Appointments.requested_start >' => $now,
            ])
            ->all();

        $requests = [];
        foreach ($pending as $appointment) {
            $waited = max(0, intdiv($nowTs - $appointment->created->getTimestamp(), 3600));
            $until = max(0, intdiv($appointment->requested_start->getTimestamp() - $nowTs, 3600));

            $level = 'new';
            if ($waited >= self::OVERDUE_AFTER_HOURS) {
                $level = 'late';
            } elseif ($waited >= self::WAITING_AFTER_HOURS) {
                $level = 'warn';
            }
            $urgent = $until < self::URGENT_SESSION_WITHIN_HOURS;

            $requests[] = [
                'appointment' => $appointment,
                'waited' => $waited,
                'until' => $until,
                'level' => $level,
                'urgent' => $urgent,
            ];
        }
        // Urgent first, then the longest waiting.
        usort($requests, static fn(array $a, array $b): int => [(int)$b['urgent'], $b['waited']] <=> [(int)$a['urgent'], $a['waited']]);

        // Student updates the counselor has not looked at yet (future sessions only):
        // cancellations and answers to a suggested new time.
        $studentUpdates = [];
        $unread = $this->fetchTable('Notifications')->find('unread')
            ->where(['Notifications.user_id' => $me, 'Notifications.type IN' => ['appointment_cancelled', 'proposal_accepted', 'proposal_declined']])
            ->orderBy(['Notifications.created' => 'DESC', 'Notifications.id' => 'DESC'])
            ->limit(20)
            ->all();
        foreach ($unread as $notification) {
            $when = $notification->details()['when'] ?? null;
            if ($when !== null && strtotime((string)$when) > $nowTs) {
                $studentUpdates[] = $notification;
            }
        }

        $pendingCount = count($requests);
        $this->set(compact('pendingCount', 'todaySessions', 'weekCount', 'requests', 'studentUpdates'));
    }
}