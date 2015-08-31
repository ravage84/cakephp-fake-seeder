<?php

App::uses('SeederTaskBase', 'FakeSeeder.Console');

/**
 * Example Seeder Task
 */
class ExampleSeederTask extends SeederTaskBase {

	/**
	 * The config key to read, 'FakeSeeder.$_configKey.valueKey'
	 *
	 * Does not need to be set, uses the name of the seeder class by default, e.g. "Article" for "ArticleSeederShell".
	 *
	 * @var string
	 */
	protected $_configKey = 'ExampleKey';

	/**
	 * The name of the model to seed
	 *
	 * Does not need to be set, uses the name of the seeder class by default, e.g. "Article" for "ArticleSeederTask".
	 *
	 * @var string
	 */
	protected $_modelName = 'NotExample';

	/**
	 * Models to truncate
	 *
	 * Does not need to be set, uses the name of the seeder class by default, e.g. "Article" for "ArticleSeederTask".
	 *
	 * @var array
	 */
	protected $_modelsToTruncate = array('SomeModel', 'AnotherModel');

	/**
	 * Fixture records which are processed additionally and before the faked ones
	 *
	 * @var array
	 */
	protected $_fixtureRecords = array(
		array(
			'id' => 1,
			'name' => 'abc',
		),
		array(
			'id' => 2,
			'name' => 'def',
		),
	);

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
	protected $_minRecords = 100;

	/**
	 * Set the maximum record count for a seeder task, null means no maximum.
	 *
	 * @var null|int
	 */
	protected $_maxRecords = 20000;

	/**
	 * The records to seed, optional
	 *
	 * @var null|int
	 */
	protected $_records = 12345;

	/**
	 * Whether or not to validate the seeding data when saving, optional
	 *
	 * @var null|bool|string
	 * @see Model::saveAll() See for possible values for `validate`.
	 */
	protected $_validateSeeding = true;

	/**
	 * The seeding number for Faker to use
	 *
	 * @var null|bool|int
	 * @see Generator::seed Faker's seed method.
	 */
	protected $_seedingNumber = 123456;

	/**
	 * Whether or not to truncate the model , optional.
	 *
	 * @var null|bool
	 */
	protected $_noTruncate = false;

	/**
	 * Set/get the field formatters
	 *
	 * {@inheritDoc}
	 */
	public function fieldFormatters() {
		parent::fieldFormatters();
		$faker = $this->faker;

		return $this->_mergeFieldFormatters(
			array(
				'name' => function ($state) use ($faker) {
					return $faker->unique()->name;
				},
			)
		);
	}

	/**
	 * Set/get state per record
	 *
	 * Can be overridden to return some state with data per record.
	 *
	 * @return array The state per record.
	 */
	public function recordState() {
		return array(
			'foo' => 'bar',
		);
	}
}
