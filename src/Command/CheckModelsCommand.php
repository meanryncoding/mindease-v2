<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\ORM\Locator\LocatorAwareTrait;
use Throwable;

/**
 * Quick health check for the appointment-related models.
 * Run with: bin\cake check_models
 */
class CheckModelsCommand extends Command
{
    use LocatorAwareTrait;

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $io->out('Timezone: ' . date_default_timezone_get());

        $checks = [
            'Appointments' => fn() => $this->fetchTable('Appointments')->find()->count(),
            'CounselorAvailability' => fn() => $this->fetchTable('CounselorAvailability')->find()->count(),
            'Notifications' => fn() => $this->fetchTable('Notifications')->find()->count(),
            'Users -> StudentAppointments' => fn() => $this->fetchTable('Users')->StudentAppointments->find()->count(),
            'Users -> CounselorAvailability' => fn() => $this->fetchTable('Users')->CounselorAvailability->find()->count(),
            'Users -> Notifications' => fn() => $this->fetchTable('Users')->Notifications->find()->count(),
            'Status column type' => fn() => $this->fetchTable('Appointments')->getSchema()->getColumnType('status'),
        ];

        $failed = false;
        foreach ($checks as $label => $check) {
            try {
                $io->out('OK    ' . $label . ': ' . $check());
            } catch (Throwable $e) {
                $failed = true;
                $io->err('FAIL  ' . $label . ': ' . $e->getMessage());
            }
        }

        if ($failed) {
            $io->err('Some checks failed. Please send this output.');

            return static::CODE_ERROR;
        }
        $io->success('All checks passed.');

        return static::CODE_SUCCESS;
    }
}