<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddMeetingLinkToAppointments extends BaseMigration
{
    public function change(): void
    {
        // Video link the counselor adds when confirming an online session.
        $this->table('appointments')
            ->addColumn('meeting_link', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'after' => 'access_needs'])
            ->update();
    }
}