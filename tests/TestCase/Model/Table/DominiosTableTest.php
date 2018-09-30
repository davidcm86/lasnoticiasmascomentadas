<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\DominiosTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\DominiosTable Test Case
 */
class DominiosTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\DominiosTable
     */
    public $Dominios;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.dominios',
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
        $config = TableRegistry::exists('Dominios') ? [] : ['className' => DominiosTable::class];
        $this->Dominios = TableRegistry::get('Dominios', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Dominios);

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
