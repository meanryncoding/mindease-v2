<?php
declare(strict_types=1);

namespace App\View\Cell;

use Cake\View\Cell;

/**
 * Bell icon in the top bar: unread count and the latest notifications.
 */
class NotificationBellCell extends Cell
{
    public function display(): void
    {
        $identity = $this->request->getAttribute('identity');
        $items = [];
        $unread = 0;

        if ($identity !== null) {
            $userId = (int)$identity->getIdentifier();
            $Notifications = $this->fetchTable('Notifications');

            $unread = $Notifications->find('unread')->where(['Notifications.user_id' => $userId])->count();
            $items = $Notifications->find()
                ->where(['Notifications.user_id' => $userId])
                ->orderBy(['Notifications.created' => 'DESC', 'Notifications.id' => 'DESC'])
                ->limit(6)
                ->all()
                ->toList();
        }

        $this->set(compact('items', 'unread'));
    }
}