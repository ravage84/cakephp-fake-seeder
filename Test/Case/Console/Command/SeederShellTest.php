<?php

App::uses('AppShell', 'Console/Command');
App::uses('SeederShell', 'FakeSeeder.Console/Command');
App::uses('SeederTaskBase', 'FakeSeeder.Console');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');

/**
 * A test implementation of SeederShell with seeders defined
 */
class SeederShellWithTasks extends SeederShell {

	/**
	 * Two seeders configured
	 *
	 * @var array
	 */
	protected $_seeders = array('Apple', 'Banana');

	/**
	 * One separate, one duplicated model
	 *
	 * @var array
	 */
	protected $_modelsToTruncate = array('Pear', 'Banana');
}

/**
 * SederShell Test
 *
 * @coversDefaultClass SeederShell
 */
class SeederShellTest extends CakeTestCase {

	/**
	 * The fixtures
	 *
	 * @var array
	 */
	public $fixtures = array(
		'plugin.fake_seeder.apple',
		'plugin.fake_seeder.banana',
		'plugin.fake_seeder.pear',
	);

	/**
	 * Disable auto loading the fixtures as we rarely need them
	 *
	 * @var bool
	 */
	public $autoFixtures = false;

	/**
	 * The shell under test
	 *
	 * @var null|SeederShell
	 */
	protected $_shell = null;

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
	protected function _createShellMock($methods, $className = 'SeederShell') {
		$out = $this->getMock('ConsoleOutput', array(), array(), '', false);
		$in = $this->getMock('ConsoleInput', array(), array(), '', false);
		$this->_shell = $this->getMock(
			$className,
			$methods,
			array($out, $out, $in)
		);
	}

	/**
	 * Test the seedability check on init
	 *
	 * @return void
	 * @covers ::initialize
	 * @covers ::_checkSeedable
	 */
	public function testNotSeedable() {
		Configure::delete('FakeSeeder.seedable');
		$this->_shell->expects($this->at(0))->method('_stop')->with(
			$this->equalTo('Seeding is not activated in configuration in "FakeSeeder.seedable"!')
		);
		$this->_shell->initialize();


		$debug = Configure::read('debug');
		Configure::write('debug', 0);
		$this->_shell->expects($this->at(1))->method('_stop')->with(
			$this->equalTo('Seeding is allowed only in debug mode!')
		);
		$this->_shell->initialize();
		Configure::write('debug', $debug);
	}

	/**
	 * Tests the getOptionParser method
	 *
	 * @return void
	 * @covers ::getOptionParser
	 */
	public function testGetOptionParser() {
		$perser = $this->_shell->getOptionParser();

		$this->assertNotEmpty($perser->description());

		$arguments = $perser->arguments();
		$this->assertArrayHasKey(0, $arguments);
		$arg = $arguments[0];
		$this->assertEquals('model', $arg->name());
		$this->assertFalse($arg->isRequired());
		$this->assertNotEmpty($arg->help());

		$options = $perser->options();

		$this->assertArrayHasKey('mode', $options);
		$this->assertArrayHasKey('locale', $options);
		$this->assertArrayHasKey('records', $options);
		$this->assertArrayHasKey('validate', $options);
		$this->assertArrayHasKey('seed', $options);
		$this->assertArrayHasKey('no-truncate', $options);

		$option = $options['mode'];
		$this->assertEquals('mode', $option->name());
		$this->assertAttributeNotEmpty('_help', $option);
		$this->assertEquals('m', $option->short());
		$this->assertFalse($option->isBoolean());
		$this->assertEquals('', $option->defaultValue());
		$this->assertAttributeEquals(array('manual', 'auto', 'mixed'), '_choices', $option);

		$option = $options['locale'];
		$this->assertEquals('locale', $option->name());
		$this->assertAttributeNotEmpty('_help', $option);
		$this->assertEquals('l', $option->short());
		$this->assertFalse($option->isBoolean());
		$this->assertEquals('', $option->defaultValue());
		$this->assertAttributeEmpty('_choices', $option);

		$option = $options['records'];
		$this->assertEquals('records', $option->name());
		$this->assertAttributeNotEmpty('_help', $option);
		$this->assertEquals('r', $option->short());
		$this->assertFalse($option->isBoolean());
		$this->assertEquals('', $option->defaultValue());
		$this->assertAttributeEmpty('_choices', $option);

		$option = $options['validate'];
		$this->assertEquals('validate', $option->name());
		$this->assertAttributeNotEmpty('_help', $option);
		$this->assertEmpty($option->short());
		$this->assertFalse($option->isBoolean());
		$this->assertEquals('', $option->defaultValue());
		$this->assertAttributeEquals(array('first', true, false), '_choices', $option);

		$option = $options['seed'];
		$this->assertEquals('seed', $option->name());
		$this->assertAttributeNotEmpty('_help', $option);
		$this->assertEquals('s', $option->short());
		$this->assertFalse($option->isBoolean());
		$this->assertEquals('', $option->defaultValue());
		$this->assertAttributeEmpty('_choices', $option);

		$option = $options['no-truncate'];
		$this->assertEquals('no-truncate', $option->name());
		$this->assertAttributeNotEmpty('_help', $option);
		$this->assertEmpty($option->short());
		$this->assertTrue($option->isBoolean());
		$this->assertEquals('', $option->defaultValue());
		$this->assertAttributeEmpty('_choices', $option);

		$this->assertNotEmpty($perser->epilog());
	}

	/**
	 * Test the main method when quitting the tasks prompt
	 *
	 * @return void
	 * @covers ::main
	 * @covers ::_promptForSeeder
	 */
	public function testMainQuitPrompt() {
		$tasks = array('Apple', 'Banana');
		$this->_shell->expects($this->at(0))->method('_getSeederTasks')->will($this->returnValue($tasks));
		$this->_shell->expects($this->at(1))->method('out')->with(
			$this->equalTo('Choose one seeder shell task to execute:')
		);
		$this->_shell->expects($this->at(2))->method('out')->with(
			$this->equalTo('1. Apple')
		);
		$this->_shell->expects($this->at(3))->method('out')->with(
			$this->equalTo('2. Banana')
		);
		$this->_shell->expects($this->at(4))->method('in')->with(
			$this->equalTo("Enter a number from the list above,\n" .
				"type in the name of another seeder shell task,\n" .
				"type in the name of a model,\n" .
				"or 'q' to exit"))->will($this->returnValue('q'));

		$this->_shell->main();

		$this->assertArrayNotHasKey(0, $this->_shell->args);
	}

	/**
	 * Test the main method when providing an invalid seeder name
	 *
	 * @return void
	 * @covers ::main
	 * @covers ::_promptForSeeder
	 */
	public function testMainInvalidSeederPrompt() {
		$tasks = array('Apple', 'Banana');
		$this->_shell->expects($this->at(0))->method('_getSeederTasks')->will($this->returnValue($tasks));
		$this->_shell->expects($this->at(4))->method('in')->will($this->returnValue('999999999'));
		$this->_shell->expects($this->at(5))->method('err')->with(
			$this->equalTo("The seeder shell task name you supplied was empty,\n" .
				"or the number you selected was not a valid option. Please try again.")
		);

		$this->_shell->main();

		$this->assertArrayNotHasKey(0, $this->_shell->args);
	}

	/**
	 * Test the main method when executing one of the prompted (non-existing) seeders by name
	 *
	 * @return void
	 * @covers ::main
	 * @covers ::_promptForSeeder
	 * @covers ::_executeSeederTask
	 */
	public function testMainExecuteByNameSeederPrompt() {
		$this->_createShellMock(
			array('out', 'in', '_getSeederTasks', '_executeSeederTask')
		);

		$tasks = array('Apple', 'Banana');
		$this->_shell->params['no-truncate'] = 'foo';
		$this->_shell->expects($this->at(0))->method('_getSeederTasks')->will($this->returnValue($tasks));
		$this->_shell->expects($this->at(4))->method('in')->will($this->returnValue('Apple'));
		$this->_shell->expects($this->at(5))->method('out')->with(
			$this->equalTo('Execute Apple seeder...')
		);
		$this->_shell->expects($this->at(6))->method('_executeSeederTask')->with(
			$this->equalTo('Apple'), $this->equalTo('foo')
		);

		$this->_shell->main();

		$this->assertEquals('Apple', $this->_shell->args[0]);
	}

	/**
	 * Test the main method when executing one of the prompted (non-existing) seeders by number
	 *
	 * @return void
	 * @covers ::main
	 * @covers ::_promptForSeeder
	 * @covers ::_executeSeederTask
	 */
	public function testMainExecuteByNumberSeederPrompt() {
		$this->_createShellMock(
			array('out', 'in', '_getSeederTasks', '_executeSeederTask')
		);

		$tasks = array('Apple', 'Banana');
		$this->_shell->params['no-truncate'] = 'foo';
		$this->_shell->expects($this->at(0))->method('_getSeederTasks')->will($this->returnValue($tasks));
		$this->_shell->expects($this->at(4))->method('in')->will($this->returnValue('1'));
		$this->_shell->expects($this->at(5))->method('out')->with(
			$this->equalTo('Execute Apple seeder...')
		);
		$this->_shell->expects($this->at(6))->method('_executeSeederTask')->with(
			$this->equalTo('Apple'), $this->equalTo('foo')
		);

		$this->_shell->main();

		$this->assertEquals('Apple', $this->_shell->args[0]);
	}

	/**
	 * Test the main method when executing a seeder given by parameter
	 *
	 * @return void
	 * @covers ::main
	 */
	public function testMainExecuteSeederGivenByParameter() {
		$this->_createShellMock(
			array('out', '_executeSeederTask')
		);

		$this->_shell->args[0] = 'Apple';
		$this->_shell->params['no-truncate'] = 'foo';

		$this->_shell->expects($this->at(0))->method('out')->with(
			$this->equalTo('Execute Apple seeder...')
		);
		$this->_shell->expects($this->at(1))->method('_executeSeederTask')->with(
			$this->equalTo("Apple"),
			$this->_shell->params['no-truncate']
		);

		$this->_shell->main();
	}

	/**
	 * Test the main method when executing a seeder given by parameter when there are seeders defined
	 *
	 * Should execute the given seeder nonetheless.
	 *
	 * @return void
	 * @covers ::main
	 */
	public function testMainExecuteSeederGivenByParameterWithDefinedSeeders() {
		$this->_createShellMock(
			array('out', '_executeSeederTask'),
			'SeederShellWithTasks'
		);

		$this->_shell->args[0] = 'Apple';
		$this->_shell->params['no-truncate'] = 'foo';

		$this->_shell->expects($this->at(0))->method('out')->with(
			$this->equalTo('Execute Apple seeder...')
		);
		$this->_shell->expects($this->at(1))->method('_executeSeederTask')->with(
			$this->equalTo("Apple"),
			$this->_shell->params['no-truncate']
		);

		$this->_shell->main();
	}

	/**
	 * Test the _executeSeederTask method when trying to execute an existing seeder
	 *
	 * @return void
	 * @covers ::main
	 * @covers ::_executeSeederTask
	 */
	public function testMainExecuteSeederTaskByName() {
		$this->loadFixtures('Apple');

		$this->_createShellMock(
			array('out'),
			'SeederShellWithTasks'
		);
		$this->_shell->Tasks = $this->getMock(
			'TaskCollection',
			array('load'),
			array(),
			'',
			false
		);
		$seederTask = $this->getMock(
			'SeederTaskBase',
			array('initialize', 'execute', 'fieldFormatters')
		);

		$this->_shell->Tasks->expects($this->once())->method('load')->with($this->equalTo('AppleSeeder'))->will($this->returnValue($seederTask));

		$seederTask->expects($this->at(0))->method('initialize');
		$seederTask->expects($this->at(1))->method('execute');

		$this->_shell->args[0] = 'Apple';
		$this->_shell->params['no-truncate'] = 'foo';
		// Add an additional option parameter
		$this->_shell->params['records'] = 50;

		$this->_shell->main();

		$this->assertAttributeEquals($this->_shell->args, 'args', $seederTask);
		$this->assertAttributeEquals($this->_shell->params, 'params', $seederTask);
	}

	/**
	 * Test the _executeSeederTask method when trying to execute a non existing seeder
	 *
	 * Should execute the built-in DynamicModelSeeder instead.
	 *
	 * @return void
	 * @covers ::main
	 * @covers ::_executeSeederTask
	 */
	public function testMainExecuteSeederTaskDynamicModelSeeder() {
		$this->loadFixtures('Apple');

		$this->_createShellMock(
			array('out'),
			'SeederShellWithTasks'
		);
		$this->_shell->Tasks = $this->getMock(
			'TaskCollection',
			array('load'),
			array(),
			'',
			false
		);
		$seederTask = $this->getMock(
			'SeederTaskBase',
			array('initialize', 'execute', 'fieldFormatters')
		);

		$this->_shell->Tasks->expects($this->at(0))->method('load')->with($this->equalTo('AppleSeeder'))->will($this->throwException(new MissingTaskException('')));
		$this->_shell->Tasks->expects($this->at(1))->method('load')->with($this->equalTo('FakeSeeder.DynamicModelSeeder'))->will($this->returnValue($seederTask));

		$seederTask->expects($this->at(0))->method('initialize');
		$seederTask->expects($this->at(1))->method('execute');

		$this->_shell->args[0] = 'Apple';
		$this->_shell->params['no-truncate'] = 'foo';
		// Add an additional option parameter
		$this->_shell->params['records'] = 50;

		$this->_shell->main();

		$this->assertAttributeEquals($this->_shell->args, 'args', $seederTask);
		$this->assertAttributeEquals($this->_shell->params, 'params', $seederTask);
	}

	/**
	 * Test the _executeSeederTask method when trying to execute a non existing seeder for which no model/table exists
	 *
	 * @return void
	 * @covers ::main
	 * @covers ::_executeSeederTask
	 */
	public function testMainExecuteSeederTaskNoTable() {
		$this->_createShellMock(
			array('out', '_loadSeederModel'),
			'SeederShellWithTasks'
		);
		$this->_shell->Tasks = $this->getMock(
			'TaskCollection',
			array('load'),
			array(),
			'',
			false
		);
		$seederTask = $this->getMock(
			'SeederTaskBase',
			array('initialize', 'execute', 'fieldFormatters')
		);

		$this->_shell->Tasks->expects($this->once())->method('load')->with($this->equalTo('NonExistingModelSeeder'))
			->will($this->throwException(new MissingTaskException('')));
		$this->_shell->expects($this->once())->method('_loadSeederModel')->with($this->equalTo('NonExistingModel'))
			->will($this->returnValue(false));

		$seederTask->expects($this->never())->method('initialize');
		$seederTask->expects($this->never())->method('execute');

		$this->_shell->args[0] = 'NonExistingModel';
		$this->_shell->params['no-truncate'] = 'foo';

		$this->_shell->main();
	}

	/**
	 * Test the main method with seeders defined and no truncate
	 *
	 * @return void
	 * @covers ::main
	 */
	public function testMainExecuteDefinedSeedersNoTruncate() {
		$this->_createShellMock(
			array('out', '_truncateModels', '_callSeeders'),
			'SeederShellWithTasks'
		);

		// Disable truncate
		$this->_shell->params['no-truncate'] = true;

		$this->_shell->expects($this->never())->method('_truncateModels');
		$this->_shell->expects($this->at(0))->method('_callSeeders');

		$this->_shell->main();
	}

	/**
	 * Test the main method with seeders defined with truncate
	 *
	 * @return void
	 * @covers ::main
	 */
	public function testMainExecuteDefinedSeedersWithTruncate() {
		$this->_createShellMock(
			array('out', '_truncateModels', '_callSeeders'),
			'SeederShellWithTasks'
		);

		// Enable truncate
		$this->_shell->params['no-truncate'] = false;

		$this->_shell->expects($this->at(0))->method('_truncateModels');
		$this->_shell->expects($this->at(1))->method('_callSeeders');

		$this->_shell->main();
	}

	/**
	 * Test the _truncateModels method
	 *
	 * @return void
	 * @covers ::_truncateModels
	 * @covers ::_getModelsToTruncateFromSeederTask
	 */
	public function testTruncateModels() {
		$this->loadFixtures('Apple', 'Banana', 'Pear');

		$this->_createShellMock(
			array('out', '_callSeeders', '_getModelTruncator'),
			'SeederShellWithTasks'
		);
		$this->_shell->Tasks = $this->getMock(
			'TaskCollection',
			array('load'),
			array(),
			'',
			false
		);
		$seederTask = $this->getMock(
			'SeederTaskBase',
			array('getModelsToTruncate', 'fieldFormatters')
		);
		$modelTruncator = $this->getMock(
			'ShellModelTruncator',
			array('truncateModels'),
			array(),
			'',
			false
		);

		$this->_shell->expects($this->at(0))->method('out')->with($this->equalTo('Truncating models...'));
		$this->_shell->expects($this->at(1))->method('_getModelTruncator')->will($this->returnValue($modelTruncator));
		$this->_shell->expects($this->at(2))->method('out')->with($this->equalTo('Finished truncating models.'));

		$this->_shell->Tasks->expects($this->at(0))->method('load')->with($this->equalTo('AppleSeeder'))->will($this->returnValue($seederTask));
		$this->_shell->Tasks->expects($this->at(1))->method('load')->with($this->equalTo('BananaSeeder'))->will($this->returnValue($seederTask));

		$seederTask->expects($this->at(0))->method('getModelsToTruncate')->will($this->returnValue(array('Apple')));
		$seederTask->expects($this->at(1))->method('getModelsToTruncate')->will($this->returnValue(array('Banana')));

		$modelsToTruncate = array('Apple', 'Banana', 'Pear');
		$modelTruncator->expects($this->at(0))->method('truncateModels')->with($this->equalTo($modelsToTruncate));

		// Enable truncate
		$this->_shell->params['no-truncate'] = false;

		$this->_shell->main();
	}

	/**
	 * Test the _callSeeders method
	 *
	 * @return void
	 * @covers ::_callSeeders
	 */
	public function testCallSeeders() {
		$this->_createShellMock(
			array('out', '_executeSeederTask'),
			'SeederShellWithTasks'
		);

		$this->_shell->expects($this->at(0))->method('out')->with($this->equalTo('Execute seeders...'));
		$this->_shell->expects($this->at(1))->method('out')->with($this->equalTo('Execute Apple seeder...'));
		$this->_shell->expects($this->at(2))->method('_executeSeederTask')->with(
			$this->equalTo("Apple"),
			true
		);
		$this->_shell->expects($this->at(3))->method('out')->with($this->equalTo('Execute Banana seeder...'));
		$this->_shell->expects($this->at(4))->method('_executeSeederTask')->with(
			$this->equalTo("Banana"),
			true
		);
		$this->_shell->expects($this->at(0))->method('out')->with($this->equalTo('Execute seeders...'));

		// Set no-truncate to something else than boolean true,
		// so we can verify _executeSeederTask gets called with literal true
		$this->_shell->params['no-truncate'] = 'foo';

		$this->_shell->main();
	}

}