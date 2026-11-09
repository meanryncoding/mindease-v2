<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Appointment;
use Cake\Event\EventInterface;

/**
 * Shared behaviour for every counselor page: only counselors get in,
 * and they all use the counselor layout.
 */
abstract class CounselorBaseController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $identity = $this->Authentication->getIdentity();
        if ($identity === null || $identity->get('role') !== 'counselor') {
            return $this->redirect('/');
        }
        $this->viewBuilder()->setLayout('counselor');

        return null;
    }

    /** The signed-in counselor's user id. */
    protected function counselorId(): int
    {
        return (int)$this->Authentication->getIdentity()->getIdentifier();
    }

    /** Show a one-time message at the top of the next page. */
    protected function notice(string $type, string $text): void
    {
        $this->request->getSession()->write('notice', ['type' => $type, 'text' => $text]);
    }

    /**
     * Store an in-app notification for a student. The title is an English message key and the
     * details are JSON, so the text can be shown in the student's language later.
     */
    protected function notifyStudent(
        string $type,
        string $title,
        Appointment $appointment,
        ?string $note = null,
        ?string $when = null,
    ): void
    {
        $Notifications = $this->fetchTable('Notifications');
        $Notifications->save($Notifications->newEntity([
            'user_id' => $appointment->student_id,
            'type' => $type,
            'title' => $title,
            'body' => json_encode([
                'counselor' => (string)$this->Authentication->getIdentity()->get('name'),
                'when' => $when ?? $appointment->requested_start->format('Y-m-d H:i:s'),
                'reference' => $appointment->reference,
                'note' => $note,
            ], JSON_UNESCAPED_UNICODE),
            'link' => '/student/sessions/' . $appointment->id,
        ]));
    }
}