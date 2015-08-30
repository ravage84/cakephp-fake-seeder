<?php

App::uses('SeederTaskBase', 'FakeSeeder.Console');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('ShellSeedProcessor', 'FakeSeeder.Lib');

/**
 * A testable implementation of SeederTaskBase
 */
class TestSeederTaskBase extends SeederTaskBase {

	/**
	 * A test proxy method for _mergeFieldFormatters
	 */
	public function mergeFieldFormatters($fieldFormatters) {
		return $this->_mergeFieldFormatters($fieldFormatters);
	}

	/**
	 * A test proxy method for _getParameter
	 */
	public function getParameter($configKey, $propertyName, $defaultValue = null) {
		return parent::_getParameter($configKey, $propertyName, $defaultValue);
	}

	/**
	 * Needs to be implemented
	 */
	public function fieldFormatters() {
		$this->_fieldFormatters;
	}

	/**
	 * A test proxy method for _getSeederConfigKey
	 */
	public function getSeederConfigKey() {
		return $this->_getSeederConfigKey();
	}

	/**
	 * A test proxy method for _getSeederNamePrefix
	 */
	public function getSeederNamePrefix() {
		return $this->_getSeederNamePrefix();
	}

	/**
	 * A test proxy method for _getSeederShellName
	 */
	public function getSeederShellName() {
		return $this->_getSeederShellName();
	}

}

/**
 * A testable implementation of SeederTaskBase with all proprties set
 */
class PropertiesSetSeederTaskBase extends TestSeederTaskBase {

	/**
	 * The config key to read, 'FakeSeeder.$_configKey.valueKey'
	 *
	 * Does not need to be set, uses the name of the seeder class by default, e.g. "Article" for "ArticleSeederShell".
	 *
	 * @var string
	 */
	protected $_configKey = 'CustomConfigKey';

	/**
	 * The name of the model to seed
	 *
	 * Does not need to be set, uses the name of the seeder class by default, e.g. "Article" for "ArticleSeederTask".
	 *
	 * @var string
	 */
	protected $_modelName = 'CustomModelName';

	/**
	 * Models to truncate
	 *
	 * Does not need to be set, uses the name of the seeder class by default, e.g. "Article" for "ArticleSeederTask".
	 *
	 * @var array
	 */
	protected $_modelsToTruncate = array('AnotherModel');

	/**
	 * Fixture records which are processed additionally and before the faked ones
	 *
	 * @var array
	 */
	protected $_fixtureRecords = array(array('foo' => 'bar'));

	/**
	 * The fields and their formatter
	 *
	 * @var array
	 */
	protected $_fieldFormatters = array();

	/**
	 * The seeding mode, optional.
	 *
	 * @var null|string
	 */
	protected $_mode = 'mixed';

	/**
	 * The locale to use for Faker, optional
	 *
	 * @var null|int
	 */
	protected $_locale = 'de_DE';

	/**
	 * Set the minimum record count for a seeder task, null means no minimum.
	 *
	 * @var null|int
	 */
	protected $_minRecords = 5;

	/**
	 * Set the maximum record count for a seeder task, null means no maximum.
	 *
	 * @var null|int
	 */
	protected $_maxRecords = 100;

	/**
	 * The records to seed, optional
	 *
	 * @var null|int
	 */
	protected $_records = 50;

	/**
	 * Whether or not to validate the seeding data when saving, optional
	 *
	 * @var null|bool|string
	 * @see Model::saveAll() See for possible values for `validate`.
	 */
	protected $_validateSeeding = false;

	/**
	 * The seeding number for Faker to use
	 *
	 * @var null|bool|int
	 * @see Generator::seed Faker's seed method.
	 */
	protected $_seedingNumber = 123456789;

	/**
	 * Whether or not to truncate the model , optional.
	 *
	 * @var null|bool
	 */
	protected $_noTruncate = true;
}

/**
 * SeederTaskBase Test
 *
 * @coversDefaultClass SeederTaskBase
 */
class SeederTaskBaseTest extends CakeTestCase {

	/**
	 * The task under test
	 *
	 * @var null|TestSeederTaskBase
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
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell', '_getParameter')
		);
	}

	/**
	 * Creates a shell mock
	 *
	 * @param array $methods A list of methods to mock.
	 * @param string $className Optional name of the seeder shell class to mock.
	 * @return void
	 */
	protected function _createShellMock($methods, $className = 'TestSeederTaskBase') {
		$out = $this->getMock('ConsoleOutput', array(), array(), '', false);
		$in = $this->getMock('ConsoleInput', array(), array(), '', false);
		$this->_task = $this->getMock(
			$className,
			$methods,
			array($out, $out, $in)
		);
	}

	/**
	 * Tests the execute method
	 *
	 * @return void
	 * @covers ::execute
	 * @covers ::_getFaker
	 * @covers ::_truncateModels
	 */
	public function testExecute() {
		$this->_createShellMock(
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell', 'getLocale', 'getSeedingNumber', 'getNoTruncate', 'getModelsToTruncate', '_getModelTruncator', '_getSeedProcessor')
		);
		$seedProcessor = $this->getMock(
			'ShellSeedProcessor',
			array('processFixtures', 'sowSeeds'),
			array(),
			'',
			false
		);
		$modelTruncator = $this->getMock(
			'ShellModelTruncator',
			array('truncateModels'),
			array(),
			'',
			false
		);
		$modelsToTruncate = array('Apple', 'Banana');

		$this->_task->expects($this->at(0))->method('getLocale')->will($this->returnValue('de_DE'));
		$this->_task->expects($this->at(1))->method('getSeedingNumber')->will($this->returnValue(123456));
		$this->_task->expects($this->at(2))->method('out')->with($this->equalTo('Create Faker instance with "de_DE" locale...'));
		$this->_task->expects($this->at(3))->method('out')->with($this->equalTo("Use seed '123456' for Faker."));
		$this->_task->expects($this->at(4))->method('getNoTruncate')->will($this->returnValue(false));
		$this->_task->expects($this->at(5))->method('getModelsToTruncate')->will($this->returnValue($modelsToTruncate));
		$this->_task->expects($this->at(6))->method('_getModelTruncator')->will($this->returnValue($modelTruncator));
		$this->_task->expects($this->at(7))->method('_getSeedProcessor')->will($this->returnValue($seedProcessor));

		$modelTruncator->expects($this->at(0))->method('truncateModels')->with($this->equalTo($modelsToTruncate));

		$seedProcessor->expects($this->at(0))->method('processFixtures');
		$seedProcessor->expects($this->at(1))->method('sowSeeds');

		$this->_task->execute();
	}

	/**
	 * Tests the getModelsToTruncate method
	 *
	 * @return void
	 * @covers ::getModelsToTruncate
	 */
	public function testGetModelsToTruncate() {
		$this->_createShellMock(
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell', 'getModelName')
		);
		$expected = array('YetAnotherModel');
		$this->_task->expects($this->at(0))->method('getModelName')->will($this->returnValue('YetAnotherModel'));

		$result = $this->_task->getModelsToTruncate();

		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the fixtureRecords method
	 *
	 * @return void
	 * @covers ::getModelsToTruncate
	 */
	public function testGetModelsToTruncatePropertySet() {
		$this->_createShellMock(
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell', '_getParameter'),
			'PropertiesSetSeederTaskBase'
		);

		$result = $this->_task->getModelsToTruncate();
		$expected = array('AnotherModel');

		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the getModelsToTruncate method
	 *
	 * @return void
	 * @covers ::fixtureRecords
	 */
	public function testFixtureRecords() {
		$this->_createShellMock(
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell', '_getParameter'),
			'PropertiesSetSeederTaskBase'
		);

		$result = $this->_task->fixtureRecords();
		$expected = array(array('foo' => 'bar'));

		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the _mergeFieldFormatters method
	 *
	 * @return void
	 * @covers ::_mergeFieldFormatters
	 */
	public function testMergeFieldFormatters() {
		$result = $this->_task->mergeFieldFormatters(array());
		$this->assertEmpty($result);

		$fieldFormatters = array('foo');
		$result = $this->_task->mergeFieldFormatters($fieldFormatters);
		$this->assertEquals($fieldFormatters, $result);

		$fieldFormatters = array('bar');
		$result = $this->_task->mergeFieldFormatters($fieldFormatters);
		$expected = array('foo', 'bar');
		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the recordState method
	 *
	 * @return void
	 * @covers ::recordState
	 */
	public function testRecordsState() {
		$result = $this->_task->recordState();
		$expected = array();

		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the getModelName method
	 *
	 * @return void
	 * @covers ::getModelName
	 */
	public function testGetModelName() {
		$this->_createShellMock(
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell', '_getSeederNamePrefix')
		);
		$expected = 'AModelName';
		$this->_task->expects($this->at(0))->method('_getSeederNamePrefix')->will($this->returnValue($expected));
		$result = $this->_task->getModelName();
		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the getModelName method
	 *
	 * @return void
	 * @covers ::getModelName
	 */
	public function testGetModelNamePropertySet() {
		$this->_createShellMock(
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell'),
			'PropertiesSetSeederTaskBase'
		);
		$expected = 'CustomModelName';
		$result = $this->_task->getModelName();
		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the getSeedingMode method
	 *
	 * @return void
	 * @covers ::getSeedingMode
	 */
	public function testGetSeedingMode() {
		$value = 'auto';
		$this->_task->expects($this->at(0))->method('_getParameter')->will($this->returnValue($value));
		$result = $this->_task->getSeedingMode();
		$this->assertEquals($value, $result);
	}

	/**
	 * Tests the getSeedingMode method
	 *
	 * @return void
	 * @covers ::getLocale
	 */
	public function testGetLocale() {
		$value = 'de_DE';
		$this->_task->expects($this->at(0))->method('_getParameter')->will($this->returnValue($value));
		$result = $this->_task->getLocale();
		$this->assertEquals($value, $result);
	}

	/**
	 * Tests the getRecordsCount method
	 *
	 * @return void
	 * @covers ::getRecordsCount
	 * @covers ::_enforceRecordMaximum
	 * @covers ::_enforceRecordMinimum
	 */
	public function testGetRecordsCount() {
		$value = '50';
		$this->_task->expects($this->at(0))->method('_getParameter')->will($this->returnValue($value));
		$this->_task->expects($this->never())->method('out');
		$result = $this->_task->getRecordsCount();
		$this->assertEquals($value, $result);
	}

	/**
	 * Tests the getRecordsCount method when records is set too high
	 *
	 * @return void
	 * @covers ::getRecordsCount
	 * @covers ::_enforceRecordMaximum
	 */
	public function testGetRecordsCountTooHigh() {
		$this->_createShellMock(
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell', '_getParameter'),
			'PropertiesSetSeederTaskBase'
		);

		$value = '500';
		$this->_task->expects($this->at(0))->method('_getParameter')->will($this->returnValue($value));
		$this->_task->expects($this->at(1))->method('out')->with($this->equalTo('500 records exceed the allowed maximum amount. Reducing it to 100 records.'));
		$result = $this->_task->getRecordsCount();

		$this->assertEquals(100, $result);
	}

	/**
	 * Tests the getRecordsCount method when records is set too low
	 *
	 * @return void
	 * @covers ::getRecordsCount
	 * @covers ::_enforceRecordMinimum
	 */
	public function testGetRecordsCountTooLow() {
		$this->_createShellMock(
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell', '_getParameter'),
			'PropertiesSetSeederTaskBase'
		);

		$value = '1';
		$this->_task->expects($this->at(0))->method('_getParameter')->will($this->returnValue($value));
		$this->_task->expects($this->at(1))->method('out')->with($this->equalTo('1 records fall below the allowed minimum amount. Increasing it to 5 records.'));
		$result = $this->_task->getRecordsCount();

		$this->assertEquals(5, $result);
	}

	/**
	 * Tests the getValidateSeeding method
	 *
	 * @return void
	 * @covers ::getValidateSeeding
	 */
	public function testGetValidateSeeding() {
		$value = 'first';
		$this->_task->expects($this->at(0))->method('_getParameter')->will($this->returnValue($value));
		$result = $this->_task->getValidateSeeding();
		$this->assertEquals($value, $result);
	}

	/**
	 * Tests the getSeedingNumber method
	 *
	 * @return void
	 * @covers ::getSeedingNumber
	 */
	public function testGetSeedingNumber() {
		$value = '123456';
		$this->_task->expects($this->at(0))->method('_getParameter')->will($this->returnValue($value));
		$result = $this->_task->getSeedingNumber();
		$this->assertEquals($value, $result);
	}

	/**
	 * Tests the getNoTruncate method
	 *
	 * @return void
	 * @covers ::getNoTruncate
	 */
	public function testGetNoTruncate() {
		$value = true;
		$this->_task->expects($this->at(0))->method('_getParameter')->will($this->returnValue($value));
		$result = $this->_task->getNoTruncate();
		$this->assertEquals($value, $result);
	}

	/**
	 * Tests the _getParameter method for the default value
	 *
	 * @return void
	 * @covers ::_getParameter
	 */
	public function testGetParameterDefaultValue() {
		$this->_createShellMock(
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell', '_getSeederTasks')
		);

		$this->_task->expects($this->at(0))->method('out')->with($this->equalTo('Parameter "records"  not given/configured, falling back to default "500".'));

		$configKey = 'records';
		$propertyName = '_records';
		$defaultValue = '500';

		$this->_task->params[$configKey] = null;
		Configure::write('FakeSeeder.TestSeederTaskBase.' . $configKey, null);
		Configure::write('FakeSeeder.' . $configKey, null);

		$result = $this->_task->getParameter($configKey, $propertyName, $defaultValue);
		$this->assertEquals($defaultValue, $result);
	}

	/**
	 * Tests the _getParameter method when the property is set
	 *
	 * @return void
	 * @covers ::_getParameter
	 */
	public function testGetParameterPropertySet() {
		$this->_createShellMock(
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell', '_getSeederTasks')
		);

		$this->_createShellMock(array('out'), 'PropertiesSetSeederTaskBase');

		$this->_task->expects($this->at(0))->method('out')->with($this->equalTo('Parameter "records" set in class: "50"'));

		$configKey = 'records';
		$propertyName = '_records';

		$this->_task->params[$configKey] = null;
		Configure::write('FakeSeeder.TestSeederTaskBase.' . $configKey, null);
		Configure::write('FakeSeeder.' . $configKey, null);

		$result = $this->_task->getParameter($configKey, $propertyName);
		$this->assertEquals(50, $result);
	}

	/**
	 * Tests the _getParameter method when the general config is set
	 *
	 * @return void
	 * @covers ::_getParameter
	 */
	public function testGetParameterGeneralConfgigSet() {
		$this->_createShellMock(
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell', '_getSeederTasks')
		);

		$this->_task->expects($this->at(0))->method('out')->with($this->equalTo('Parameter "records" configured in general seeder configuration: "75"'));

		$configKey = 'records';
		$propertyName = '_records';

		$this->_task->params[$configKey] = null;
		Configure::write('FakeSeeder.TestSeederTaskBase.' . $configKey, null);
		Configure::write('FakeSeeder.' . $configKey, 75);

		$result = $this->_task->getParameter($configKey, $propertyName);
		$this->assertEquals(75, $result);
	}

	/**
	 * Tests the _getParameter method when the seeder specific config is set
	 *
	 * @return void
	 * @covers ::_getParameter
	 */
	public function testGetParameterSeederConfgigSet() {
		$this->_createShellMock(
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell', '_getSeederTasks')
		);

		$this->_createShellMock(array('out', '_getSeederConfigKey'));

		$this->_task->expects($this->at(0))->method('_getSeederConfigKey')->will($this->returnValue('FakeSeeder.TestSeederTaskBase'));
		$this->_task->expects($this->at(1))->method('out')->with($this->equalTo('Parameter "records" configured in seeder specific configuration: "62"'));

		$configKey = 'records';
		$propertyName = '_records';

		$this->_task->params[$configKey] = null;
		Configure::write('FakeSeeder.TestSeederTaskBase.' . $configKey, 62);
		Configure::write('FakeSeeder.' . $configKey, null);

		$result = $this->_task->getParameter($configKey, $propertyName);
		$this->assertEquals(62, $result);
	}

	/**
	 * Tests the _getParameter method when the parameter is set
	 *
	 * @return void
	 * @covers ::_getParameter
	 */
	public function testGetParameterParameterSet() {
		$this->_createShellMock(
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell', '_getSeederTasks')
		);

		$this->_task->expects($this->at(0))->method('out')->with($this->equalTo('Parameter "records" given through CLI parameter: "24"'));

		$configKey = 'records';
		$propertyName = '_records';

		$this->_task->params[$configKey] = 24;
		Configure::write('FakeSeeder.TestSeederTaskBase.' . $configKey, null);
		Configure::write('FakeSeeder.' . $configKey, null);

		$result = $this->_task->getParameter($configKey, $propertyName);
		$this->assertEquals(24, $result);
	}

	/**
	 * Tests the _getSeederConfigKey method
	 *
	 * @return void
	 * @covers ::_getSeederConfigKey
	 */
	public function testGetSeederConfigKey() {
		$this->_createShellMock(
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell', '_getSeederShellName', '_getSeederNamePrefix')
		);
		$this->_task->expects($this->at(0))->method('_getSeederNamePrefix')->will($this->returnValue('TaskName'));
		$this->_task->expects($this->at(1))->method('_getSeederShellName')->will($this->returnValue('FakeSeeder'));
		$expected = 'FakeSeeder.TaskName';
		$result = $this->_task->getSeederConfigKey();
		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the _getSeederConfigKey method
	 *
	 * @return void
	 * @covers ::_getSeederConfigKey
	 */
	public function testGetSeederConfigKeyPropertySet() {
		$this->_createShellMock(
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop', '_showInfo', 'dispatchShell', '_getSeederShellName'),
			'PropertiesSetSeederTaskBase'
		);
		$this->_task->expects($this->at(0))->method('_getSeederShellName')->will($this->returnValue('FakeSeeder'));
		$expected = 'FakeSeeder.CustomConfigKey';
		$result = $this->_task->getSeederConfigKey();
		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the _getSeederNamePrefix method
	 *
	 * @return void
	 * @covers ::_getSeederNamePrefix
	 */
	public function testGetSeederNamePrefix() {
		$out = $this->getMock('ConsoleOutput', array(), array(), '', false);
		$in = $this->getMock('ConsoleInput', array(), array(), '', false);
		$this->_task = $this->getMock(
			'TestSeederTaskBase',
			null,
			array($out, $out, $in),
			'TestNameSeederTask',
			false
		);

		$expected = 'TestName';
		$result = $this->_task->getSeederNamePrefix();
		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the _getSeederShellName method
	 *
	 * @return void
	 * @covers ::_getSeederShellName
	 */
	public function testGetSeederShellName() {
		$this->assertEquals('FakeSeeder', $this->_task->getSeederShellName());
	}
}
