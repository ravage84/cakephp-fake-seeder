<?php

App::uses('ShellDispatcher', 'Console');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('Shell', 'Console');
App::uses('ShellModelTruncator', 'FakeSeeder.Lib');

/**
 * ShellModelTruncator Test
 *
 * @coversDefaultClass ShellModelTruncator
 */
class ShellModelTruncatorTest extends CakeTestCase {

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
	 * The shell object
	 *
	 * @var null|Shell
	 */
	protected $_shell = null;

	/**
	 * The object under test
	 *
	 * @var null|ShellModelTruncator
	 */
	protected $_truncator = null;

	/**
	 * Setup the object under test
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$out = $this->getMock('ConsoleOutput', array(), array(), '', false);
		$in = $this->getMock('ConsoleInput', array(), array(), '', false);
		$this->_shell = $this->getMock('Shell',
			array('in', 'out', 'hr', 'createFile', 'error', 'err', '_stop'),
			array($out, $out, $in)
		);

		$this->_truncator = new ShellModelTruncator($this->_shell);
	}

	/**
	 * Test the constructor
	 *
	 * @return void
	 * @covers ::__construct
	 */
	public function testConstruct() {
		$truncator = new ShellModelTruncator($this->_shell);
		$this->assertAttributeInstanceOf('Shell', '_shell', $truncator);
	}

	/**
	 * Tests the truncateModels function
	 *
	 * @return void
	 * @covers ::truncateModels
	 */
	public function testTruncateModels() {
		$this->_shell->expects($this->at(0))->method('out')->with($this->equalTo('Truncate model Apple...'));
		$this->_shell->expects($this->at(1))->method('out')->with($this->equalTo('Truncate model Banana...'));
		$this->_shell->expects($this->at(2))->method('out')->with($this->equalTo('Truncate model Pear...'));

		$models = array('Apple', 'Banana', 'Pear');
		$this->_truncator->truncateModels($models);
	}
}
