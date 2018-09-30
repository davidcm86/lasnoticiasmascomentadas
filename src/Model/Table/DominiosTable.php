<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Dominios Model
 *
 * @property \App\Model\Table\PeriodicosTable|\Cake\ORM\Association\HasMany $Periodicos
 * @property \App\Model\Table\TopNoticiasTable|\Cake\ORM\Association\HasMany $TopNoticias
 *
 * @method \App\Model\Entity\Dominio get($primaryKey, $options = [])
 * @method \App\Model\Entity\Dominio newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Dominio[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Dominio|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Dominio patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Dominio[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Dominio findOrCreate($search, callable $callback = null, $options = [])
 */
class DominiosTable extends Table
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

        $this->setTable('dominios');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->hasMany('Periodicos', [
            'foreignKey' => 'dominio_id'
        ]);
        $this->hasMany('TopNoticias', [
            'foreignKey' => 'dominio_id'
        ]);
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
            ->allowEmpty('dominio');

        return $validator;
    }
}
