<?php

App::uses('DynamicModelSeederTask', 'FakeSeeder.Console/Command/Task');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');

/**
 * A testable implementation of DynamicModelSeederTask
 */
class TestDynamicModelSeederTask extends DynamicModelSeederTask {

	/**
	 * Set a specific ModelName to be executed
	 *
	 * @var array
	 */
	public $args = array(0 => 'ModelName');

	/**
	 * Set some test dummy formatters
	 *
	 * @var array
	 */
	protected $_fieldFormatters = array('foo' => 'bar');

}

/**
 * DynamicModelSeederTask Test
 *
 * @coversDefaultClass DynamicModelSeederTask
 */
class DynamicModelSeederTaskTest extends CakeTestCase {

	/**
	 * The task under test
	 *
	 * @var null|TestDynamicModelSeederTask
	 */
	protected $_task = null;

	/**
	 * Setup the shell under test
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->_createShellMock(
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell', '_getSeederTasks')
		);
	}

	/**
	 * Creates a shell mock
	 *
	 * @param array $methods A list of methods to mock.
	 * @param string $className Optional name of the seeder shell class to mock.
	 * @return void
	 */
	protected function _createShellMock($methods, $className = 'TestDynamicModelSeederTask') {
		$out = $this->getMock('ConsoleOutput', array(), array(), '', false);
		$in = $this->getMock('ConsoleInput', array(), array(), '', false);
		$this->_task = $this->getMock(
			$className,
			$methods,
			array($out, $out, $in)
		);
	}

	/**
	 * Tests the _getModelName method
	 *
	 * @return void
	 * @covers ::getModelName
	 */
	public function testGetModelName() {
		$result = $this->_task->getModelName();
		$expected = 'ModelName';
		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the _fieldFormatters method
	 *
	 * @return void
	 * @covers ::fieldFormatters
	 */
	public function testFieldFormatters() {
		$result = $this->_task->fieldFormatters();
		$expected = array('foo' => 'bar');
		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the _getSeedingMode method
	 *
	 * @return void
	 * @covers ::getSeedingMode
	 */
	public function testGetSeedingMode() {
		$result = $this->_task->getSeedingMode();
		$this->assertEquals('auto', $result);
	}
}