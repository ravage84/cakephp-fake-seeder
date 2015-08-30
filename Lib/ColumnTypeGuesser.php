<?php

use \Faker\Generator;

/**
 * Column Type Guesser
 *
 * Tries to guess the Faker formatter provider bases upon
 * a column type.
 *
 * @see Model::schema CakePHP's schema inspection.
 */
class ColumnTypeGuesser {

	/**
	 * A faker generator instance
	 *
	 * @var Generator
	 */
	protected $_generator;

	/**
	 * Constructor
	 *
	 * @param Generator $generator A faker generator instance.
	 */
	public function __construct(Generator $generator) {
		$this->_generator = $generator;
	}

	/**
	 * Guess the formatter
	 *
	 * @param array $column The column info.
	 * @return callable|null
	 */
	public function guessFormat($column) {
		$generator = $this->_generator;

		switch ($column['type']) {
			case 'boolean':
				return function () use ($generator) {
					return $generator->boolean;
				};
			case 'integer':
				return function () use ($generator) {
					return $generator->randomNumber(9);
				};
			case 'biginteger':
				return function () use ($generator) {
					return $generator->randomNumber(18);
				};
			case 'decimal':
			case 'float':
				return function () use ($generator) {
					return $generator->randomFloat();
				};
			case 'uuid':
				return function () use ($generator) {
					return $generator->uuid();
				};
			case 'string':
				$length = $column['length'];
				$string = str_repeat("?", $length);
				if ($length <= '5') {
					return function () use ($generator, $string) {
						return $generator->lexify($string);
					};
				}
				return function () use ($generator, $length) {
					return $generator->text($length);
				};
			case 'text':
				return function () use ($generator) {
					return $generator->text();
				};
			case 'date':
			case 'datetime':
			case 'timestamp':
			case 'time':
				return function () use ($generator) {
					return $generator->iso8601();
				};

			case 'binary':
			default:
				return null;
		}
	}
}
