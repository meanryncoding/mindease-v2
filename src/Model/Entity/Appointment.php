<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\Entity;

/**
 * Appointment entity.
 *
 * Only the fields a student fills in can be mass-assigned. Ownership, status,
 * reference, consent time and the counselor's decision are set explicitly in controllers.
 * "consent" is a form-only checkbox (not a database column).
 *
 * @property int $id
 * @property string $reference
 * @property int $student_id
 * @property int $counselor_id
 * @property \Cake\I18n\DateTime $requested_start
 * @property int $duration_minutes
 * @property string $mode
 * @property string|null $message
 * @property string|null $access_needs
 * @property \Cake\I18n\DateTime $consent_at
 * @property \App\Model\Enum\AppointmentStatus $status
 * @property \Cake\I18n\DateTime|null $proposed_start
 * @property string|null $decision_note
 * @property \Cake\I18n\DateTime|null $decided_at
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class Appointment extends Entity
{
    protected array $_accessible = [
        'requested_start' => true,
        'mode' => true,
        'message' => true,
        'access_needs' => true,
        'consent' => true,
    ];

    /**
     * Where in-person sessions take place. Change it with
     * Configure::write('MindEase.inPersonLocation', '...') in config/app.php.
     */
    public static function inPersonLocation(): string
    {
        return (string)Configure::read('MindEase.inPersonLocation', __('Counseling Unit (room confirmed by your counselor)'));
    }

    /** Human-readable description of how the session takes place. */
    public function modeLabel(): string
    {
        if ($this->mode === 'in_person') {
            return __('In person · {0}', self::inPersonLocation());
        }

        return __('Online · Private video session');
    }
}