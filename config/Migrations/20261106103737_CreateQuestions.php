<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateQuestions extends BaseMigration
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
    $table = $this->table('questions');
    $table
        ->addColumn('section', 'string', ['limit' => 1, 'null' => false])
        ->addColumn('question_code', 'string', ['limit' => 10, 'null' => false])
        ->addColumn('question_text', 'text', ['null' => false])
        ->addColumn('response_type', 'string', ['limit' => 20, 'null' => false, 'default' => 'scale'])
        ->addColumn('max_score', 'integer', ['null' => false, 'default' => 3])
        ->addColumn('is_crisis_trigger', 'boolean', ['null' => false, 'default' => false])
        ->addColumn('order_num', 'integer', ['null' => false])
        ->addColumn('status', 'string', ['limit' => 20, 'null' => false, 'default' => 'active'])
        ->addIndex(['question_code'], ['unique' => true])
        ->create();
}
}
