<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PeriodicosTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PeriodicosTable Test Case
 */
class PeriodicosTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\PeriodicosTable
     */
    public $Periodicos;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.periodicos',
        'app.top_noticias'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Periodicos') ? [] : ['className' => PeriodicosTable::class];
        $this->Periodicos = TableRegistry::get('Periodicos', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Periodicos);

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
}
