<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * In-app notifications shown under the bell icon.
 */
class NotificationsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('notifications');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        // Notifications only have a creation time.
        $this->addBehavior('Timestamp', [
            'events' => ['Model.beforeSave' => ['created' => 'new']],
        ]);

        $this->belongsTo('Users', ['foreignKey' => 'user_id', 'joinType' => 'INNER']);
    }

    public function findUnread(SelectQuery $query): SelectQuery
    {
        return $query->where([$this->aliasField('read_at') . ' IS' => null]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('type')->maxLength('type', 40)->requirePresence('type', 'create')->notEmptyString('type');
        $validator
            ->scalar('title')->maxLength('title', 150)->requirePresence('title', 'create')->notEmptyString('title');
        $validator
            ->allowEmptyString('body');
        $validator
            ->allowEmptyString('link')->maxLength('link', 255);

        return $validator;
    }
}