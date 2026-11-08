<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * A bookable slot.
 *
 * @property int $id
 * @property int $counselor_id
 * @property \Cake\I18n\DateTime $start_at
 * @property \Cake\I18n\DateTime $end_at
 * @property \Cake\I18n\DateTime $created
 */
class CounselorAvailability extends Entity
{
    protected array $_accessible = [
        'counselor_id' => true,
        'start_at' => true,
        'end_at' => true,
    ];
}