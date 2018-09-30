<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Dominio Entity
 *
 * @property int $id
 * @property string $dominio
 *
 * @property \App\Model\Entity\Periodico[] $periodicos
 * @property \App\Model\Entity\TopNoticia[] $top_noticias
 */
class Dominio extends Entity
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
