<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Enum\AppointmentStatus;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Database\Type\EnumType;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use DateTimeInterface;

/**
 * Appointment requests between a student and a counselor.
 */
class AppointmentsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('appointments');
        $this->setDisplayField('reference');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        // Both sides are rows of the users table.
        $this->belongsTo('Students', [
            'className' => 'Users',
            'foreignKey' => 'student_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Counselors', [
            'className' => 'Users',
            'foreignKey' => 'counselor_id',
            'joinType' => 'INNER',
        ]);
    }

    /** Map the status column to the AppointmentStatus enum. */
    public function getSchema(): TableSchemaInterface
    {
        return parent::getSchema()->setColumnType('status', EnumType::from(AppointmentStatus::class));
    }

    /**
     * Validates what the student submits on the booking form.
     * Student, counselor, status and reference are set by the controller, not the form.
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('requested_start', 'create')
            ->notEmptyDateTime('requested_start', __('Please choose a date and time.'))
            ->add('requested_start', 'future', [
                'rule' => function ($value): bool {
                    $time = $value instanceof DateTimeInterface ? $value->getTimestamp() : strtotime((string)$value);

                    return $time !== false && $time > time();
                },
                'message' => __('Please choose a time in the future.'),
            ]);

        $validator
            ->allowEmptyString('message')
            ->maxLength('message', 1000, __('Please keep this under 1000 characters.'));

        $validator
            ->allowEmptyString('access_needs')
            ->maxLength('access_needs', 500, __('Please keep this under 500 characters.'));

        // The student must agree to the confidentiality information before booking.
        $validator
            ->requirePresence('consent', 'create', __('Please confirm that you understand the confidentiality information.'))
            ->add('consent', 'agreed', [
                'rule' => fn($value): bool => in_array($value, [1, '1', true, 'true', 'on'], true),
                'message' => __('Please confirm that you understand the confidentiality information.'),
            ]);

        return $validator;
    }
}