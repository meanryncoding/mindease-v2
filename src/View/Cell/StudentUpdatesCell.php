<?php
declare(strict_types=1);

namespace App\View\Cell;

use Cake\View\Cell;

/**
 * "Updates for you" card on the student overview: unread notifications.
 */
class StudentUpdatesCell extends Cell
{
    private const SHOW = 5;

    public function display(): void
    {
        $identity = $this->request->getAttribute('identity');
        $items = [];
        $total = 0;

        if ($identity !== null) {
            $query = $this->fetchTable('Notifications')->find('unread')
                ->where(['Notifications.user_id' => (int)$identity->getIdentifier()]);

            $total = $query->count();
            $items = $query
                ->orderBy(['Notifications.created' => 'DESC', 'Notifications.id' => 'DESC'])
                ->limit(self::SHOW)
                ->all()
                ->toList();
        }

        $this->set(compact('items', 'total'));
    }
}