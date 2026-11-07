<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'Lorem ipsum dolor sit amet',
                'email' => 'Lorem ipsum dolor sit amet',
                'password' => 'Lorem ipsum dolor sit amet',
                'role' => 'Lorem ipsum dolor ',
                'status' => 'Lorem ipsum dolor ',
                'student_id' => 'Lorem ipsum dolor ',
                'faculty' => 'Lorem ipsum dolor sit amet',
                'program' => 'Lorem ipsum dolor sit amet',
                'year_of_study' => 1,
                'specialisation' => 'Lorem ipsum dolor sit amet',
                'created' => '2026-11-06 15:31:36',
                'modified' => '2026-11-06 15:31:36',
            ],
        ];
        parent::init();
    }
}
