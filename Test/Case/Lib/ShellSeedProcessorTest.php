<?php

App::uses('ShellDispatcher', 'Console');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('SeederTaskBase', 'FakeSeeder.Console');
App::uses('ShellSeedProcessor', 'FakeSeeder.Lib');
App::uses('ColumnTypeGuesser', 'FakeSeeder.Lib');

/**
 * ShellSeedProcessor Test
 *
 * @coversDefaultClass ShellSeedProcessor
 */
class ShellSeedProcessorTest extends CakeTestCase {

	/**
	 * The fixtures
	 *
	 * @var array
	 */
	public $fixtures = array(
		'plugin.fake_seeder.apple',
	);

	/**
	 * Disable auto loading the fixtures as we rarely need them
	 *
	 * @var bool
	 */
	public $autoFixtures = false;

	/**
	 * The shell object
	 *
	 * @var null|Shell
	 */
	protected $_seeder = null;

	/**
	 * The object under test
	 *
	 * @var null|ShellSeedProcessor
	 */
	protected $_processor = null;

	/**
	 * Setup the object under test
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$out = $this->getMock('ConsoleOutput', array(), array(), '', false);
		$in = $this->getMock('ConsoleInput', array(), array(), '', false);
		$this->_seeder = $this->getMock('SeederTaskBase',
			array(),
			array($out, $out, $in)
		);
	}

	/**
	 * Create a ShellSeedProcessor mock
	 *
	 * @param array $methods The methods to mock.
	 * @return void
	 */
	protected function _createProcessorMock($methods) {
		$this->_processor = $this->getMock(
			'ShellSeedProcessor',
			$methods,
			array($this->_seeder)
		);
	}

	/**
	 * Test the constructor
	 *
	 * @return void
	 * @covers ::__construct
	 */
	public function testConstruct() {
		$processor = new ShellSeedProcessor($this->_seeder);
		$this->assertAttributeInstanceOf('SeederTaskBase', '_seeder', $processor);
	}

	/**
	 * Tests the processFixtures method
	 *
	 * @return void
	 * @covers ::processFixtures
	 */
	public function testProcessFixtures() {
		$this->_createProcessorMock(array('saveSeeds'));
		$fixtures = array(array('Model' => array('foo' => 'bar')));

		$this->_seeder->expects($this->at(0))->method('fixtureRecords')->will($this->returnValue($fixtures));
		$this->_processor->expects($this->at(0))->method('saveSeeds');

		$this->_processor->processFixtures();
	}

	/**
	 * Tests the processFixtures method with no fixtures set
	 *
	 * @return void
	 * @covers ::processFixtures
	 */
	public function testProcessFixturesNoFixturesSet() {
		$this->_createProcessorMock(array('saveSeeds'));

		$fixtures = array();

		$this->_seeder->expects($this->at(0))->method('fixtureRecords')->will($this->returnValue($fixtures));
		$this->_processor->expects($this->never())->method('saveSeeds');

		$this->_processor->processFixtures();
	}

	/**
	 * Tests the sowSeeds method when there are no field formatters
	 *
	 * @return void
	 * @covers ::sowSeeds
	 */
	public function testSowSeedsNoFieldFormatters() {
		$this->_createProcessorMock(array('getFieldFormatters', 'createSeed', 'saveSeeds'));

		$this->_seeder->expects($this->at(0))->method('getModelName')->will($this->returnValue('Apple'));
		$this->_seeder->expects($this->at(1))->method('getRecordsCount')->will($this->returnValue(5));
		$this->_seeder->expects($this->at(2))->method('out')->with($this->equalTo('Sowing 5 seeds for model Apple'));
		$this->_seeder->expects($this->at(3))->method('out')->with($this->equalTo('No field formatters configured, aborting.'));

		$this->_processor->expects($this->at(0))->method('getFieldFormatters')->will($this->returnValue(array()));
		$this->_processor->expects($this->never())->method('createSeed');
		$this->_processor->expects($this->never())->method('saveSeeds');

		$this->_processor->sowSeeds();
	}

	/**
	 * Tests the sowSeeds method when empty seeds get created
	 *
	 * @return void
	 * @covers ::sowSeeds
	 */
	public function testSowSeedsCreatedSeedEmpty() {
		$this->_createProcessorMock(array('getFieldFormatters', 'createSeed', 'saveSeeds'));

		$this->_seeder->expects($this->at(0))->method('getModelName')->will($this->returnValue('Apple'));
		$this->_seeder->expects($this->at(1))->method('getRecordsCount')->will($this->returnValue(5));
		$this->_seeder->expects($this->at(2))->method('out')->with($this->equalTo('Sowing 5 seeds for model Apple'));
		$this->_seeder->expects($this->at(3))->method('out')->with($this->equalTo('Created seed is empty! Check the field formatters.'));
		$this->_seeder->expects($this->at(4))->method('out')->with($this->equalTo('Finished sowing 5 seeds for model Apple'));

		$this->_processor->expects($this->at(0))->method('getFieldFormatters')->will($this->returnValue(array('NotEmpty')));
		$this->_processor->expects($this->at(1))->method('createSeed')->will($this->returnValue(array()));
		$this->_processor->expects($this->at(2))->method('saveSeeds');

		$this->_processor->sowSeeds();
	}

	/**
	 * Tests the sowSeeds method
	 *
	 * @return void
	 * @covers ::sowSeeds
	 */
	public function testSowSeeds() {
		$this->_createProcessorMock(array('getFieldFormatters', 'createSeed', 'saveSeeds'));

		$this->_seeder->expects($this->at(0))->method('getModelName')->will($this->returnValue('Apple'));
		$this->_seeder->expects($this->at(1))->method('getRecordsCount')->will($this->returnValue(5));
		$this->_seeder->expects($this->at(2))->method('out')->with($this->equalTo('Sowing 5 seeds for model Apple'));
		$this->_seeder->expects($this->at(4))->method('out')->with($this->equalTo('.'), $this->equalTo(0));
		$this->_seeder->expects($this->at(5))->method('out')->with($this->equalTo('.'), $this->equalTo(0));
		$this->_seeder->expects($this->at(6))->method('out')->with($this->equalTo('.'), $this->equalTo(0));
		$this->_seeder->expects($this->at(7))->method('out')->with($this->equalTo('.'), $this->equalTo(0));
		$this->_seeder->expects($this->at(8))->method('out')->with($this->equalTo(' 5 / 5'), $this->equalTo(1));
		$this->_seeder->expects($this->at(9))->method('out')->with($this->equalTo('Finished sowing 5 seeds for model Apple'));

		$this->_processor->expects($this->at(0))->method('getFieldFormatters')->will($this->returnValue(array('NotEmpty')));
		$this->_processor->expects($this->exactly(5))->method('createSeed')->will($this->returnValue(array('NotEmpty')));
		$this->_processor->expects($this->at(2))->method('saveSeeds');

		$this->_processor->sowSeeds();
	}

	/**
	 * Tests the sowSeeds method when sowing more than 50 seeds
	 *
	 * @return void
	 * @covers ::sowSeeds
	 */
	public function testSowSeedsFithyPlus() {
		$this->_createProcessorMock(array('getFieldFormatters', 'createSeed', 'saveSeeds'));

		$this->_seeder->expects($this->at(0))->method('getModelName')->will($this->returnValue('Apple'));
		$this->_seeder->expects($this->at(1))->method('getRecordsCount')->will($this->returnValue(55));
		$this->_seeder->expects($this->at(3))->method('out')->with($this->equalTo('.'), $this->equalTo(0));
		$this->_seeder->expects($this->at(53))->method('out')->with($this->equalTo(' 50 / 55'), $this->equalTo(1));
		$this->_seeder->expects($this->at(58))->method('out')->with($this->equalTo('.'), $this->equalTo(0));
		$this->_seeder->expects($this->at(59))->method('out')->with(
			$this->equalTo(str_repeat(' ', 46) . '55 / 55', 1)
		);

		$this->_processor->expects($this->at(0))->method('getFieldFormatters')->will($this->returnValue(array('NotEmpty')));
		$this->_processor->expects($this->any())->method('createSeed')->will($this->returnValue(array('NotEmpty')));
		$this->_processor->expects($this->at(2))->method('saveSeeds');

		$this->_processor->sowSeeds();
	}

	/**
	 * Tests the getFieldFormatters method for 'manual' mode
	 *
	 * @return void
	 * @covers ::getFieldFormatters
	 */
	public function testGetFieldFormattersManualMode() {
		$this->_createProcessorMock(array('_guessFieldFormatters'));

		$expected = array('field formatters from manual mode');

		$this->_seeder->expects($this->at(0))->method('getSeedingMode')->will($this->returnValue('manual'));
		$this->_seeder->expects($this->at(1))->method('fieldFormatters')->will($this->returnValue($expected));
		$this->_processor->expects($this->never())->method('_guessFieldFormatters');

		$result = $this->_processor->getFieldFormatters();
		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the getFieldFormatters method for 'auto' mode
	 *
	 * @return void
	 * @covers ::getFieldFormatters
	 */
	public function testGetFieldFormattersAutolMode() {
		$this->_createProcessorMock(array('_guessFieldFormatters'));

		$expected = array('field formatters from auto mode');

		$this->_seeder->expects($this->at(0))->method('getSeedingMode')->will($this->returnValue('auto'));
		$this->_seeder->expects($this->never())->method('fieldFormatters');
		$this->_processor->expects($this->at(0))->method('_guessFieldFormatters')->will($this->returnValue($expected));

		$result = $this->_processor->getFieldFormatters();
		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the getFieldFormatters method for 'mixed' mode
	 *
	 * @return void
	 * @covers ::getFieldFormatters
	 */
	public function testGetFieldFormattersMixedMode() {
		$this->_createProcessorMock(array('_guessFieldFormatters'));

		$manualMode = array('field formatters from manual mode');
		$autoMode = array('field formatters from auto mode');
		$expected = array_merge(
			$autoMode,
			$manualMode
		);

		$this->_seeder->expects($this->at(0))->method('getSeedingMode')->will($this->returnValue('mixed'));
		$this->_seeder->expects($this->once())->method('fieldFormatters')->will($this->returnValue($manualMode));
		$this->_processor->expects($this->once())->method('_guessFieldFormatters')->will($this->returnValue($autoMode));

		$result = $this->_processor->getFieldFormatters();
		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the getFieldFormatters method for an invalid mode
	 *
	 * @return void
	 * @covers ::getFieldFormatters
	 */
	public function testGetFieldFormattersInvalidMode() {
		$this->_createProcessorMock(array('_guessFieldFormatters'));

		$this->_seeder->expects($this->at(0))->method('getSeedingMode')->will($this->returnValue('foo'));
		$this->_seeder->expects($this->never())->method('fieldFormatters');
		$this->_processor->expects($this->never())->method('_guessFieldFormatters');

		$result = $this->_processor->getFieldFormatters();
		$this->assertEmpty($result);
	}

	/**
	 * Tests the _guessFieldFormatters method
	 *
	 * @return void
	 * @covers ::_guessFieldFormatters
	 * @covers ::_getColumns
	 */
	public function testGuessFieldFormatters() {
		$this->_createProcessorMock(array('_getModel', '_getColumnTypeGuesser'));

		$model = $this->getMock('Apple', array('schema'));
		$model->primaryKey = 'id';

		$schema = array(
			'id' => array(
				'type' => 'integer',
				'null' => false,
				'default' => NULL,
				'length' => 11,
				'unsigned' => false,
				'key' => 'primary',
			),
			'field1' => array(
				'type' => 'string',
				'null' => false,
				'default' => NULL,
				'length' => 255,
				'collate' => 'latin1_swedish_ci',
				'charset' => 'latin1',
			),
			'field2' =>	array(
				'type' => 'datetime',
				'null' => true,
				'default' => NULL,
				'length' => NULL,
			),
		);

		$guesser = $this->getMock(
			'ColumnTypeGuesser',
			array(),
			array(),
			'',
			false
		);

		$guessedFormats = 'guessed formatter';

		$this->_seeder->expects($this->at(0))->method('getSeedingMode')->will($this->returnValue('auto'));
		$this->_processor->expects($this->at(0))->method('_getModel')->will($this->returnValue($model));
		$model->expects($this->at(0))->method('schema')->will($this->returnValue($schema));
		$this->_processor->expects($this->at(1))->method('_getColumnTypeGuesser')->will($this->returnValue($guesser));
		$guesser->expects($this->at(0))->method('guessFormat')
			->with($this->equalTo($schema['field1']))
			->will($this->returnValue($guessedFormats));
		$guesser->expects($this->at(1))->method('guessFormat')
			->with($this->equalTo($schema['field2']))
			->will($this->returnValue($guessedFormats));

		$result = $this->_processor->getFieldFormatters();
		$expected = array(
			'field1' => $guessedFormats,
			'field2' =>	$guessedFormats,
		);
		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the createSeed method
	 *
	 * @return void
	 * @covers ::createSeed
	 */
	public function testCreateSeed() {
		$this->_createProcessorMock(array('_getModel'));

		$this->_seeder->expects($this->at(0))->method('getModelName')->will($this->returnValue('ModelName'));
		$this->_seeder->expects($this->at(1))->method('recordState')->will($this->returnValue('state1'));

		$callable = function ($state) {
			return $state;
		};

		$fieldFormatters = array(
			'field1' => $callable,
			'field2' => $callable,
		);
		$result = $this->_processor->createSeed($fieldFormatters);
		$expected = array(
			'ModelName' => array(
				'field1' => 'state1',
				'field2' => 'state1',
			),
		);
		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the saveSeeds method when validate is 'false'
	 *
	 * @return void
	 * @covers ::saveSeeds
	 */
	public function testSaveSeedsValidateFalse() {
		$this->_createProcessorMock(array('_getModel'));
		$seeds = array('seeds');
		$model = $this->getMock('Apple', array('saveAll'));
		$options = array(
			'validate' => false
		);

		$this->_seeder->expects($this->at(0))->method('getValidateSeeding')->will($this->returnValue('false'));
		$this->_processor->expects($this->at(0))->method('_getModel')->will($this->returnValue($model));
		$model->expects($this->at(0))->method('saveAll')->with(
			$this->equalTo($seeds),
			$this->equalTo($options)
		)->will($this->returnValue(true));

		$this->_seeder->expects($this->never())->method('out');

		$this->_processor->saveSeeds($seeds);
	}

	/**
	 * Tests the saveSeeds method when validate casts to true
	 *
	 * @return void
	 * @covers ::saveSeeds
	 */
	public function testSaveSeedsValidateTrue() {
		$this->_createProcessorMock(array('_getModel'));
		$seeds = array('seeds');
		$model = $this->getMock('Apple', array('saveAll'));
		$options = array(
			'validate' => true
		);

		$this->_seeder->expects($this->at(0))->method('getValidateSeeding')->will($this->returnValue('true'));
		$this->_processor->expects($this->at(0))->method('_getModel')->will($this->returnValue($model));
		$model->expects($this->at(0))->method('saveAll')->with(
			$this->equalTo($seeds),
			$this->equalTo($options)
		)->will($this->returnValue(true));

		$this->_seeder->expects($this->never())->method('out');

		$this->_processor->saveSeeds($seeds);
	}

	/**
	 * Tests the saveSeeds method when saving fails
	 *
	 * @return void
	 * @covers ::saveSeeds
	 */
	public function testSaveSeedsNotSaved() {
		$this->_createProcessorMock(array('_getModel'));
		$seeds = array('seeds');
		$model = $this->getMock('Apple', array('saveAll'));
		$options = array(
			'validate' => 'first'
		);

		$model->validationErrors = 'test123';

		$this->_seeder->expects($this->at(0))->method('getValidateSeeding')->will($this->returnValue('first'));
		$this->_processor->expects($this->at(0))->method('_getModel')->will($this->returnValue($model));

		$model->expects($this->at(0))->method('saveAll')->with(
			$this->equalTo($seeds),
			$this->equalTo($options)
		)->will($this->returnValue(false));

		$this->_seeder->expects($this->at(1))->method('out')->with($this->equalTo('Seeds could not be saved successfully!'));
		$this->_seeder->expects($this->at(2))->method('out')->with($this->equalTo("Data validation errors: 'test123'"));

		$this->_processor->saveSeeds($seeds);
	}
}
