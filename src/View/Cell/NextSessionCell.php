<?php
declare(strict_types=1);

namespace App\View\Cell;

use App\Model\Enum\AppointmentStatus;
use Cake\I18n\DateTime;
use Cake\View\Cell;

/**
 * "Your next session" card on the student overview.
 */
class NextSessionCell extends Cell
{
    public function display(): void
    {
        $identity = $this->request->getAttribute('identity');
        $next = null;

        if ($identity !== null) {
            $open = array_map(
                static fn(AppointmentStatus $status): string => $status->value,
                [AppointmentStatus::Pending, AppointmentStatus::Approved, AppointmentStatus::RescheduleProposed],
            );

            $next = $this->fetchTable('Appointments')->find()
                ->contain(['Counselors'])
                ->where([
                    'Appointments.student_id' => (int)$identity->getIdentifier(),
                    'Appointments.status IN' => $open,
                    'Appointments.requested_start >' => DateTime::now(),
                ])
                ->orderBy(['Appointments.requested_start' => 'ASC'])
                ->first();
        }

        $this->set(compact('next'));
    }
}