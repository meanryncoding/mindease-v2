<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Bookable time slots published by counselors (one row = one slot).
 */
class CounselorAvailabilityTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('counselor_availability');
        $this->setDisplayField('start_at');
        $this->setPrimaryKey('id');

        // Slots only have a creation time.
        $this->addBehavior('Timestamp', [
            'events' => ['Model.beforeSave' => ['created' => 'new']],
        ]);

        $this->belongsTo('Counselors', [
            'className' => 'Users',
            'foreignKey' => 'counselor_id',
            'joinType' => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('start_at', 'create')->notEmptyDateTime('start_at')
            ->requirePresence('end_at', 'create')->notEmptyDateTime('end_at');

        return $validator;
    }
}