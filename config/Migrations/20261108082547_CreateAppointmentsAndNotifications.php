<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateAppointmentsAndNotifications extends BaseMigration
{
    public function up(): void
    {
        // Foreign key columns must match users.id exactly (signed or unsigned, int or bigint).
        $row = $this->fetchRow(
            "SELECT COLUMN_TYPE AS column_type FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'id'"
        );
        $columnType = strtolower((string)(is_array($row) ? ($row['column_type'] ?? $row[0] ?? '') : ''));
        $idType = str_contains($columnType, 'bigint') ? 'biginteger' : 'integer';
        $fk = ['signed' => !str_contains($columnType, 'unsigned')];

        // Counselors list the languages they can offer sessions in, e.g. "English, Bahasa Melayu".
        $this->table('users')
            ->addColumn('languages', 'string', ['limit' => 100, 'null' => true, 'default' => null])
            ->update();

        // Bookable slots published by counselors (one row = one slot).
        $this->table('counselor_availability')
            ->addColumn('counselor_id', $idType, $fk)
            ->addColumn('start_at', 'datetime')
            ->addColumn('end_at', 'datetime')
            ->addColumn('created', 'datetime')
            ->addIndex(['counselor_id', 'start_at'], ['unique' => true])
            ->addForeignKey('counselor_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();

        $this->table('appointments')
            ->addColumn('reference', 'string', ['limit' => 20])
            ->addColumn('student_id', $idType, $fk)
            ->addColumn('counselor_id', $idType, $fk)
            ->addColumn('requested_start', 'datetime')
            ->addColumn('duration_minutes', 'smallinteger', ['default' => 50])
            ->addColumn('mode', 'string', ['limit' => 20, 'default' => 'online'])
            ->addColumn('message', 'text', ['null' => true, 'default' => null])
            ->addColumn('access_needs', 'text', ['null' => true, 'default' => null])
            ->addColumn('consent_at', 'datetime')
            ->addColumn('status', 'string', ['limit' => 30, 'default' => 'pending'])
            ->addColumn('proposed_start', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('decision_note', 'text', ['null' => true, 'default' => null])
            ->addColumn('decided_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('created', 'datetime')
            ->addColumn('modified', 'datetime')
            ->addIndex(['reference'], ['unique' => true])
            ->addIndex(['counselor_id', 'status'])
            ->addIndex(['student_id', 'status'])
            ->addIndex(['requested_start'])
            ->addForeignKey('student_id', 'users', 'id', ['delete' => 'RESTRICT', 'update' => 'NO_ACTION'])
            ->addForeignKey('counselor_id', 'users', 'id', ['delete' => 'RESTRICT', 'update' => 'NO_ACTION'])
            ->create();

        $this->table('notifications')
            ->addColumn('user_id', $idType, $fk)
            ->addColumn('type', 'string', ['limit' => 40])
            ->addColumn('title', 'string', ['limit' => 150])
            ->addColumn('body', 'text', ['null' => true, 'default' => null])
            ->addColumn('link', 'string', ['limit' => 255, 'null' => true, 'default' => null])
            ->addColumn('read_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('created', 'datetime')
            ->addIndex(['user_id', 'read_at'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();
    }

    public function down(): void
    {
        $this->table('notifications')->drop()->save();
        $this->table('appointments')->drop()->save();
        $this->table('counselor_availability')->drop()->save();
        $this->table('users')->removeColumn('languages')->update();
    }
}