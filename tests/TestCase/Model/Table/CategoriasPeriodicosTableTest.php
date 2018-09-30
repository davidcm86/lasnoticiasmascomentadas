<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CategoriasPeriodicosTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CategoriasPeriodicosTable Test Case
 */
class CategoriasPeriodicosTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\CategoriasPeriodicosTable
     */
    public $CategoriasPeriodicos;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.categorias_periodicos',
        'app.periodicos',
        'app.top_noticias',
        'app.categorias_periodicos_has_categorias'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('CategoriasPeriodicos') ? [] : ['className' => CategoriasPeriodicosTable::class];
        $this->CategoriasPeriodicos = TableRegistry::get('CategoriasPeriodicos', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->CategoriasPeriodicos);

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
