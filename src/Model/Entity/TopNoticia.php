<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TopNoticia Entity
 *
 * @property int $id
 * @property string $titular
 * @property string $enlace
 * @property string $imagen
 * @property int $num_comentarios
 * @property int $posicion
 * @property int $periodico_id
 * @property int $estado_posicion
 * @property \Cake\I18n\FrozenTime $fecha_publicado
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Periodico $periodico
 */
class TopNoticia extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
