<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\I18n\DateTime;

/**
 * Notification list and actions, shared by students and counselors.
 */
class NotificationsController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $identity = $this->Authentication->getIdentity();
        $role = $identity?->get('role');
        if (!in_array($role, ['student', 'counselor'], true)) {
            return $this->redirect('/');
        }
        $this->viewBuilder()->setLayout((string)$role);

        return null;
    }

    /** All notifications, newest first. */
    public function index(): void
    {
        $notifications = $this->fetchTable('Notifications')->find()
            ->where(['Notifications.user_id' => $this->userId()])
            ->orderBy(['Notifications.created' => 'DESC', 'Notifications.id' => 'DESC'])
            ->limit(50)
            ->all()
            ->toList();

        $this->set(compact('notifications'));
    }

    /** Mark one notification as read, then go to the page it points to. */
    public function open(string $id)
    {
        $this->request->allowMethod(['post']);
        $Notifications = $this->fetchTable('Notifications');

        $notification = $Notifications->find()
            ->where(['Notifications.id' => (int)$id, 'Notifications.user_id' => $this->userId()])
            ->first();
        if ($notification === null) {
            throw new NotFoundException();
        }

        if ($notification->read_at === null) {
            $notification->read_at = DateTime::now();
            $Notifications->save($notification);
        }

        // Only follow links inside this site.
        $link = (string)$notification->link;
        if ($link !== '' && preg_match('#^/(?!/)#', $link) === 1) {
            return $this->redirect($link);
        }

        return $this->redirect('/notifications');
    }

    /** Mark every notification as read. */
    public function readAll()
    {
        $this->request->allowMethod(['post']);

        $this->fetchTable('Notifications')->updateAll(
            ['read_at' => DateTime::now()],
            ['user_id' => $this->userId(), 'read_at IS' => null],
        );

        return $this->redirect('/notifications');
    }

    private function userId(): int
    {
        return (int)$this->Authentication->getIdentity()->getIdentifier();
    }
}