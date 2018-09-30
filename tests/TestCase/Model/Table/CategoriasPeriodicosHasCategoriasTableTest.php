<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CategoriasPeriodicosHasCategoriasTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CategoriasPeriodicosHasCategoriasTable Test Case
 */
class CategoriasPeriodicosHasCategoriasTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\CategoriasPeriodicosHasCategoriasTable
     */
    public $CategoriasPeriodicosHasCategorias;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.categorias_periodicos_has_categorias',
        'app.categorias',
        'app.periodicos',
        'app.top_noticias',
        'app.categorias_periodicos'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('CategoriasPeriodicosHasCategorias') ? [] : ['className' => CategoriasPeriodicosHasCategoriasTable::class];
        $this->CategoriasPeriodicosHasCategorias = TableRegistry::get('CategoriasPeriodicosHasCategorias', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->CategoriasPeriodicosHasCategorias);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
