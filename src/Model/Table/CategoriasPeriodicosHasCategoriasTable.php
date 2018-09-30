<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CategoriasPeriodicosHasCategorias Model
 *
 * @property \App\Model\Table\CategoriasTable|\Cake\ORM\Association\BelongsTo $Categorias
 * @property \App\Model\Table\CategoriasPeriodicosTable|\Cake\ORM\Association\BelongsTo $CategoriasPeriodicos
 *
 * @method \App\Model\Entity\CategoriasPeriodicosHasCategoria get($primaryKey, $options = [])
 * @method \App\Model\Entity\CategoriasPeriodicosHasCategoria newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\CategoriasPeriodicosHasCategoria[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CategoriasPeriodicosHasCategoria|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CategoriasPeriodicosHasCategoria patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CategoriasPeriodicosHasCategoria[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\CategoriasPeriodicosHasCategoria findOrCreate($search, callable $callback = null, $options = [])
 */
class CategoriasPeriodicosHasCategoriasTable extends Table
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

        $this->setTable('categorias_periodicos_has_categorias');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Categorias', [
            'foreignKey' => 'categoria_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('CategoriasPeriodicos', [
            'foreignKey' => 'categorias_periodico_id',
            'joinType' => 'INNER'
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
        $rules->add($rules->existsIn(['categoria_id'], 'Categorias'));
        $rules->add($rules->existsIn(['categorias_periodico_id'], 'CategoriasPeriodicos'));

        return $rules;
    }
}
