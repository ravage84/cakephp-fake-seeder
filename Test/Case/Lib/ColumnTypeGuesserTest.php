<?php

use \Faker\Generator;

App::uses('ColumnTypeGuesser', 'FakeSeeder.Lib');

/**
 * ColumnTypeGuesser Test
 *
 * @coversDefaultClass ColumnTypeGuesser
 */
class ColumnTypeGuesserTest extends CakeTestCase {

	/**
	 * The faker instance
	 *
	 * @var null|Generator
	 */
	protected $_faker = null;

	/**
	 * The object under test
	 *
	 * @var null|ColumnTypeGuesser
	 */
	protected $_guesser = null;

	/**
	 * Setup the object under test
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->_faker = $this->getMock('\\Faker\\Generator', array('__get', '__call'));
		$this->_guesser = new ColumnTypeGuesser($this->_faker);
	}

	/**
	 * Test the constructor
	 *
	 * @return void
	 * @covers ::__construct
	 */
	public function testConstruct() {
		$guesser = new ColumnTypeGuesser($this->_faker);
		$this->assertAttributeInstanceOf('\\Faker\\Generator', '_generator', $guesser);
	}

	/**
	 * Tests the guessFormat method with various types
	 *
	 * @param string $magicMethod The name of the magic method (__call or __get).
	 * @param string $method The name of the method (e.g. randomNumber).
	 * @param array $column The column schema with type etc.
	 * @return void
	 * @covers ::guessFormat
	 * @dataProvider formatProvider
	 */
	public function testGuessFormats($magicMethod, $method, $column) {
		$this->_faker->expects($this->at(0))->method($magicMethod)->with(
			$this->equalTo($method)
		);
		$result = $this->_guesser->guessFormat($column);
		$result();
	}

	/**
	 * Tests the guessFormat method with various types resulting to null
	 *
	 * @param null|array $column The column schema with type etc.
	 * @return void
	 * @covers ::guessFormat
	 * @dataProvider specialFormatProvider
	 */
	public function testGuessSpecialFormat($column) {
		$result = $this->_guesser->guessFormat($column);
		$this->assertEquals($result, null);
	}

	/**
	 * A format data provider
	 *
	 * @return array Formats
	 */
	public function formatProvider() {
		return array(
			'boolean' => array('__get', 'boolean', array('type' => 'boolean')),
			'integer' => array('__call', 'randomNumber', array('type' => 'integer')),
			'bigInteger' => array('__call', 'randomNumber', array('type' => 'biginteger')),
			'decimal' => array('__call', 'randomFloat', array('type' => 'decimal')),
			'float' => array('__call', 'randomFloat', array('type' => 'float')),
			'uuid' => array('__call', 'uuid', array('type' => 'uuid')),
			'lexify' => array('__call', 'lexify', array('type' => 'string', 'length' => 4)),
			'string' => array('__call', 'text', array('type' => 'string', 'length' => 20)),
			'text' => array('__call', 'text', array('type' => 'text')),
			'date' => array('__call', 'iso8601', array('type' => 'date')),
			'datetime' => array('__call', 'iso8601', array('type' => 'datetime')),
			'timestamp' => array('__call', 'iso8601', array('type' => 'timestamp')),
			'time' => array('__call', 'iso8601', array('type' => 'time')),
		);
	}

	/**
	 * A format data provider for resulting to null cases
	 *
	 * @return array Formats
	 */
	public function specialFormatProvider() {
		return array(
			'null' => array(null),
			'binary' => array(
				array(
					'type' => 'binary'
				)
			),
		);
	}
}
