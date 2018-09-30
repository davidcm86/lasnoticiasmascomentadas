<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Crons Model
 *
 * @method \App\Model\Entity\Cron get($primaryKey, $options = [])
 * @method \App\Model\Entity\Cron newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Cron[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Cron|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Cron patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Cron[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Cron findOrCreate($search, callable $callback = null, $options = [])
 */
class CronsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('crons');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->allowEmpty('nombre');

        $validator
            ->allowEmpty('ejecutado');

        $validator
            ->allowEmpty('ejecutando');

        return $validator;
    }
}
