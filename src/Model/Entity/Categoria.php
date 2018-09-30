<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Categoria Entity
 *
 * @property int $id
 * @property string $nombre
 * @property string $slug
 * @property \Cake\I18n\FrozenTime $modified
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\CategoriasPeriodicosHasCategoria[] $categorias_periodicos_has_categorias
 * @property \App\Model\Entity\Periodico[] $periodicos
 */
class Categoria extends Entity
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
