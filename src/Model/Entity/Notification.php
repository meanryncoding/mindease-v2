<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Notification entity. Created by the application only, never from user input.
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $title
 * @property string|null $body
 * @property string|null $link
 * @property \Cake\I18n\DateTime|null $read_at
 * @property \Cake\I18n\DateTime $created
 */
class Notification extends Entity
{
    protected array $_accessible = [
        'user_id' => true,
        'type' => true,
        'title' => true,
        'body' => true,
        'link' => true,
        'read_at' => true,
    ];
}