<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateUsers extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/guides/writing-migrations/migration-methods.html#the-change-method
     *
     * @return void
     */
    public function change(): void
{
    $table = $this->table('users');
    $table
        ->addColumn('name', 'string', ['limit' => 100, 'null' => false])
        ->addColumn('email', 'string', ['limit' => 150, 'null' => false])
        ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
        ->addColumn('role', 'string', ['limit' => 20, 'null' => false, 'default' => 'student'])
        ->addColumn('status', 'string', ['limit' => 20, 'null' => false, 'default' => 'active'])
        ->addColumn('student_id', 'string', ['limit' => 20, 'null' => true])
        ->addColumn('faculty', 'string', ['limit' => 100, 'null' => true])
        ->addColumn('program', 'string', ['limit' => 100, 'null' => true])
        ->addColumn('year_of_study', 'integer', ['null' => true])
        ->addColumn('specialisation', 'string', ['limit' => 100, 'null' => true])
        ->addColumn('created', 'datetime', ['null' => true])
        ->addColumn('modified', 'datetime', ['null' => true])
        ->addIndex(['email'], ['unique' => true])
        ->create();
}
}
