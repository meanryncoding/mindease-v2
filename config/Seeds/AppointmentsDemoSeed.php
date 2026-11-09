<?php
declare(strict_types=1);

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Migrations\BaseSeed;

/**
 * Demo data for the booking screens: two counselors and two weeks of slots.
 * Safe to run more than once (it only adds what is missing).
 *
 * Run with: bin\cake seeds run AppointmentsDemoSeed
 */
class AppointmentsDemoSeed extends BaseSeed
{
    /** Slot start hours (24h) offered on every weekday. */
    private const SLOT_HOURS = [9, 10, 11, 14, 15, 16];

    /** Number of days ahead to create slots for. */
    private const DAYS_AHEAD = 14;

    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // 1. The existing counselor account becomes "Nadia Hassan".
        $existing = $this->fetchRow("SELECT id, status FROM users WHERE role = 'counselor' ORDER BY id LIMIT 1");
        if (!is_array($existing)) {
            echo "No counselor account found. Run UsersSeed first.\n";

            return;
        }
        $nadiaId = (int)$existing['id'];
        $status = (string)$existing['status'];
        $this->execute(
            "UPDATE users SET name = 'Nadia Hassan', specialisation = 'Student wellbeing',
             languages = 'English, Bahasa Melayu', modified = '{$now}' WHERE id = {$nadiaId}"
        );

        // 2. Add "David Lee" if he does not exist yet.
        $david = $this->fetchRow("SELECT id FROM users WHERE email = 'david.lee@uitm.edu.my'");
        if (!is_array($david)) {
            $this->table('users')->insert([[
                'name' => 'David Lee',
                'email' => 'david.lee@uitm.edu.my',
                'password' => (new DefaultPasswordHasher())->hash('Counselor#123'),
                'role' => 'counselor',
                'status' => $status,
                'specialisation' => 'Adjustment and study balance',
                'languages' => 'English, Mandarin',
                'created' => $now,
                'modified' => $now,
            ]])->save();
            $david = $this->fetchRow("SELECT id FROM users WHERE email = 'david.lee@uitm.edu.my'");
        }
        $davidId = (int)$david['id'];

        // 3. Weekday slots for the next two weeks (times use the app timezone, Asia/Kuala_Lumpur).
        $taken = [];
        foreach ($this->fetchAll('SELECT counselor_id, start_at FROM counselor_availability') as $row) {
            $taken[$row['counselor_id'] . '|' . $row['start_at']] = true;
        }

        $rows = [];
        for ($day = 1; $day <= self::DAYS_AHEAD; $day++) {
            $date = new DateTimeImmutable("today +{$day} days");
            if ((int)$date->format('N') >= 6) {
                continue; // skip Saturday and Sunday
            }
            foreach ([$nadiaId, $davidId] as $counselorId) {
                foreach (self::SLOT_HOURS as $hour) {
                    $start = $date->setTime($hour, 0);
                    $startText = $start->format('Y-m-d H:i:s');
                    if (isset($taken[$counselorId . '|' . $startText])) {
                        continue;
                    }
                    $rows[] = [
                        'counselor_id' => $counselorId,
                        'start_at' => $startText,
                        'end_at' => $start->modify('+50 minutes')->format('Y-m-d H:i:s'),
                        'created' => $now,
                    ];
                }
            }
        }

        if ($rows) {
            $this->table('counselor_availability')->insert($rows)->save();
        }
        echo 'Added ' . count($rows) . " new slots.\n";
    }
}