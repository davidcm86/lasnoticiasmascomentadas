<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TopNoticias Model
 *
 * @property \App\Model\Table\PeriodicosTable|\Cake\ORM\Association\BelongsTo $Periodicos
 *
 * @method \App\Model\Entity\TopNoticia get($primaryKey, $options = [])
 * @method \App\Model\Entity\TopNoticia newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\TopNoticia[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TopNoticia|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TopNoticia patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TopNoticia[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\TopNoticia findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TopNoticiasTable extends Table
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

        $this->setTable('top_noticias');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Periodicos', [
            'foreignKey' => 'periodico_id'
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
            ->allowEmpty('titular');

        $validator
            ->allowEmpty('enlace');

        $validator
            ->allowEmpty('imagen');

        $validator
            ->integer('num_comentarios')
            ->allowEmpty('num_comentarios');

        $validator
            ->integer('posicion')
            ->allowEmpty('posicion');

        $validator
            ->integer('estado_posicion')
            ->allowEmpty('estado_posicion');

        $validator
            ->dateTime('fecha_publicado')
            ->allowEmpty('fecha_publicado');

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
