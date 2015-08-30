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
	protected $_configKey = '';

	/**
	 * The name of the model to seed
	 *
	 * Does not need to be set, uses the name of the seeder class by default, e.g. "Article" for "ArticleSeederTask".
	 *
	 * @var string
	 */
	protected $_modelName = '';

	/**
	 * Models to truncate
	 *
	 * Does not need to be set, uses the name of the seeder class by default, e.g. "Article" for "ArticleSeederTask".
	 *
	 * @var array
	 */
	protected $_modelsToTruncate = array();

	/**
	 * Fixture records which are processed additionally and before the faked ones
	 *
	 * @var array
	 */
	protected $_fixtureRecords = array();

	/**
	 * The seeding mode, optional.
	 *
	 * @var null|string
	 */
	protected $_mode = null;

	/**
	 * The locale to use for Faker, optional
	 *
	 * @var null|int
	 */
	protected $_locale = null;

	/**
	 * Set the maximum records count for a seeder task, null means no maximum.
	 *
	 * @var null|int
	 */
	protected $_maxRecords = null;

	/**
	 * The records to seed, optional
	 *
	 * @var null|int
	 */
	protected $_records = null;

	/**
	 * Whether or not to validate the seeding data when saving, optional
	 *
	 * @var null|bool|string
	 * @see Model::saveAll() See for possible values for `validate`.
	 */
	protected $_validateSeeding = null;

	/**
	 * The seeding number for Faker to use
	 *
	 * @var null|bool|int
	 * @see Generator::seed Faker's seed method.
	 */
	protected $_seedingNumber = null;

	/**
	 * Whether or not to truncate the model , optional.
	 *
	 * @var null|bool
	 */
	protected $_noTruncate = null;

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
}
