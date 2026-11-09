<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * Notification entity. Created by the application only, never from user input.
 *
 * The title is stored as an English message key and the details as JSON, so the text
 * is built in the language of whoever reads it.
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

    /** Details stored with the notification. */
    public function details(): array
    {
        $data = json_decode((string)$this->body, true);

        return is_array($data) ? $data : [];
    }

    /** Short title in the reader's language. */
    public function headline(): string
    {
        return __($this->title);
    }

    /** One-sentence message in the reader's language. */
    public function message(): string
    {
        $data = $this->details();

        $when = '';
        if (!empty($data['when'])) {
            try {
                $when = (new DateTime((string)$data['when']))->i18nFormat('EEE, dd MMM · h:mm a');
            } catch (\Throwable) {
                $when = '';
            }
        }
        $student = (string)($data['student'] ?? __('A student'));
        $counselor = (string)($data['counselor'] ?? __('Your counselor'));

        return match ($this->type) {
            'appointment_requested' => __('{0} requested a session on {1}.', $student, $when),
            'appointment_confirmed' => __('{0} confirmed your session on {1}.', $counselor, $when),
            'appointment_declined' => __('{0} is not available on {1}.', $counselor, $when),
            'appointment_cancelled' => isset($data['student'])
                ? __('{0} cancelled the session on {1}.', $student, $when)
                : __('{0} cancelled your session on {1}.', $counselor, $when),
            'appointment_rescheduled' => __('{0} suggested a new time: {1}.', $counselor, $when),
            'proposal_accepted' => __('{0} accepted the new time: {1}.', $student, $when),
            'proposal_declined' => __('{0} declined the new time: {1}.', $student, $when),
            default => '',
        };
    }

    /** Optional note written by the counselor, or null. */
    public function note(): ?string
    {
        $note = trim((string)($this->details()['note'] ?? ''));

        return $note === '' ? null : $note;
    }
}