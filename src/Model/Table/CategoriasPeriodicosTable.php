<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CategoriasPeriodicos Model
 *
 * @property \App\Model\Table\PeriodicosTable|\Cake\ORM\Association\BelongsTo $Periodicos
 * @property \App\Model\Table\CategoriasPeriodicosHasCategoriasTable|\Cake\ORM\Association\HasMany $CategoriasPeriodicosHasCategorias
 *
 * @method \App\Model\Entity\CategoriasPeriodico get($primaryKey, $options = [])
 * @method \App\Model\Entity\CategoriasPeriodico newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\CategoriasPeriodico[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CategoriasPeriodico|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CategoriasPeriodico patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CategoriasPeriodico[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\CategoriasPeriodico findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CategoriasPeriodicosTable extends Table
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

        $this->setTable('categorias_periodicos');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Periodicos', [
            'foreignKey' => 'periodico_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('CategoriasPeriodicosHasCategorias', [
            'foreignKey' => 'categorias_periodico_id'
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
            ->requirePresence('nombre', 'create')
            ->notEmpty('nombre');

        $validator
            ->allowEmpty('slug');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['periodico_id'], 'Periodicos'));

        return $rules;
    }
}
