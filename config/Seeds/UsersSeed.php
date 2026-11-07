<?php
declare(strict_types=1);

use Migrations\BaseSeed;

/**
 * Users seed.
 */
class UsersSeed extends BaseSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/migrations/5/guides/seeding.html
     *
     * @return void
     */
    public function run(): void
{
    $hasher = new \Authentication\PasswordHasher\DefaultPasswordHasher();
    $now = date('Y-m-d H:i:s');

    $data = [
        [
            'name' => 'Aina Rahman',
            'email' => 'student@test.local',
            'password' => $hasher->hash('Student#123'),
            'role' => 'student',
            'status' => 'active',
            'student_id' => '2024000001',
            'faculty' => 'Computer and Mathematical Sciences',
            'program' => 'Information Studies',
            'year_of_study' => 3,
            'specialisation' => null,
            'created' => $now,
            'modified' => $now,
        ],
        [
            'name' => 'Nadia Hassan',
            'email' => 'counselor@test.local',
            'password' => $hasher->hash('Counselor#123'),
            'role' => 'counselor',
            'status' => 'active',
            'student_id' => null,
            'faculty' => null,
            'program' => null,
            'year_of_study' => null,
            'specialisation' => 'Student wellbeing',
            'created' => $now,
            'modified' => $now,
        ],
        [
            'name' => 'System Admin',
            'email' => 'admin@test.local',
            'password' => $hasher->hash('Admin#123'),
            'role' => 'admin',
            'status' => 'active',
            'student_id' => null,
            'faculty' => null,
            'program' => null,
            'year_of_study' => null,
            'specialisation' => null,
            'created' => $now,
            'modified' => $now,
        ],
    ];

    $this->table('users')->insert($data)->save();
}
}
