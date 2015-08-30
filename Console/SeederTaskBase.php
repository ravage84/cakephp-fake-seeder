<?php

use Faker\Factory;
use Faker\Generator;

App::uses('ShellModelTruncator', 'FakeSeeder.Lib');
App::uses('ShellSeedProcessor', 'FakeSeeder.Lib');

/**
 * Seeder Task Base
 *
 * Base class for specific seeder tasks to base upon.
 * Extending classes should be named after the model they seed + "SeederTask",
 * e.g. "ArticleSeederTask".
 *
 * @todo Consider implementing a minimum amount of records
 */
abstract class SeederTaskBase extends AppShell {

	/**
	 * Faker (generator) instance
	 *
	 * @var null|Generator
	 */
	public $faker = null;

	/**
	 * The seeds to be seeded
	 *
	 * @var array
	 */
	public $seeds = array();

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
	protected $_mode = null;

	/**
	 * The locale to use for Faker, optional
	 *
	 * @var null|int
	 */
	protected $_locale = null;

	/**
	 * Set the minimum record count for a seeder task, null means no minimum.
	 *
	 * @var null|int
	 */
	protected $_minRecords = null;

	/**
	 * Set the maximum record count for a seeder task, null means no maximum.
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
	 * Task execution method
	 *
	 * @return void
	 */
	public function execute() {
		$this->_getFaker();

		// Disable FK constraints

		if ($this->getNoTruncate() === false) {
			$this->_truncateModels();
		}

		// Process the fixtures before the fake seeds
		$seedProcessor = $this->_getSeedProcessor();
		$seedProcessor->processFixtures();
		$seedProcessor->sowSeeds();

		// Enable FK constraints, if necessary
	}

	/**
	 * Get a ShellSeedProcessor instance
	 *
	 * @return ShellSeedProcessor An instance of ShellSeedProcessor.
	 */
	protected function _getSeedProcessor() {
		return new ShellSeedProcessor($this);
	}

	/**
	 * Get the Faker generator with the (optionally) configured locale
	 *
	 * @return Generator
	 */
	protected function _getFaker() {
		$locale = $this->getLocale();
		$seed = $this->getSeedingNumber();

		$this->out(__('Create Faker instance with "%s" locale...', $locale), 1, Shell::VERBOSE);

		$this->faker = Factory::create($locale);
		if (!empty($seed)) {
			$this->out(__("Use seed '%s' for Faker.", $seed), 1, Shell::VERBOSE);
			$this->faker->seed($seed);
		}
		return $this->faker;
	}

	/**
	 * Truncate the models
	 *
	 * @return void
	 * @see ShellModelTruncator::truncateModels
	 */
	protected function _truncateModels() {
		$modelsToTruncate = $this->getModelsToTruncate();

		$modelTruncator = $this->_getModelTruncator();
		$modelTruncator->truncateModels($modelsToTruncate);
	}

	/**
	 * Get an instance of the ShellModelTruncator, for delegating the model truncation
	 *
	 * @return ShellModelTruncator The shell model truncator instance.
	 */
	protected function _getModelTruncator() {
		return new ShellModelTruncator($this);
	}

	/**
	 * Get models to truncate
	 *
	 * Returns the ones set in $_modelsToTruncate oo
	 * gets the model name based on the current
	 * seeder shell task name.
	 *
	 * @return array The models to truncate.
	 */
	public function getModelsToTruncate() {
		if (!empty($this->_modelsToTruncate)) {
			return $this->_modelsToTruncate;
		}

		$modelName = $this->getModelName();
		return array($modelName);
	}

	/**
	 * Set/get the fixture records
	 *
	 * @return array The fixture records.
	 */
	public function fixtureRecords() {
		return $this->_fixtureRecords;
	}

	/**
	 * Set/get the field formatters
	 *
	 * @return array The formatters per field.
	 * @link https://github.com/fzaninotto/Faker#formatters
	 */
	abstract public function fieldFormatters();

	/**
	 * Merges the given field formatters with the exiting ones
	 *
	 * @param array $fieldFormatters The field formatters to merge.
	 * @return array The merged field formatters.
	 */
	protected function _mergeFieldFormatters($fieldFormatters) {
		$this->_fieldFormatters = array_merge(
			$this->_fieldFormatters,
			$fieldFormatters
		);
		return $this->_fieldFormatters;
	}

	/**
	 * Set/get state per record
	 *
	 * Can be overridden to return some state with data per record.
	 *
	 * @return array The state per record.
	 */
	public function recordState() {
		return array();
	}

	/**
	 * Get the model name
	 *
	 * @return string The model name.
	 */
	public function getModelName() {
		$modelName = $this->_getSeederNamePrefix();
		if (!empty($this->_modelName)) {
			$modelName = $this->_modelName;
		}

		return $modelName;
	}

	/**
	 * Get the seeding mode
	 *
	 * @return mixed The the seeding mode.
	 */
	public function getSeedingMode() {
		$configKey = 'mode';
		$propertyName = '_mode';
		$defaultValue = 'manual';
		return $this->_getParameter($configKey, $propertyName, $defaultValue);
	}

	/**
	 * Get the locale to use for Faker
	 *
	 * @return string The locale for Faker.
	 */
	public function getLocale() {
		$configKey = 'locale';
		$propertyName = '_locale';
		$defaultValue = 'en_US';
		return $this->_getParameter($configKey, $propertyName, $defaultValue);
	}

	/**
	 * Get record count to create
	 *
	 * @return mixed The amount of records to create.
	 */
	public function getRecordsCount() {
		$configKey = 'records';
		$propertyName = '_records';
		$defaultValue = 10;

		$records = $this->_getParameter($configKey, $propertyName, $defaultValue);

		$records = $this->_enforceRecordMaximum($records);
		$records = $this->_enforceRecordMinimum($records);
		return $records;
	}

	/**
	 * Enforce the maximum amount of records to be seeded
	 *
	 * @param int $records The amount of records to check/reduce.
	 * @return int The enforced maximum amount of records.
	 */
	protected function _enforceRecordMaximum($records) {
		if (isset($this->_maxRecords) && $records > $this->_maxRecords) {
			$this->out(__('%s records exceed the allowed maximum amount. Reducing it to %s records.',
				$records, $this->_maxRecords), 1, Shell::VERBOSE);

			return $this->_maxRecords;
		}

		return $records;
	}

	/**
	 * Enforce the minimum amount of records to be seeded
	 *
	 * @param int $records The amount of records to check/increase.
	 * @return int The enforced minimum amount of records.
	 */
	protected function _enforceRecordMinimum($records) {
		if (isset($this->_minRecords) && $records < $this->_minRecords) {
			$this->out(__('%s records fall below the allowed minimum amount. Increasing it to %s records.',
				$records, $this->_minRecords), 1, Shell::VERBOSE);

			return $this->_minRecords;
		}

		return $records;
	}

	/**
	 * Get whether or not to validate seeding
	 *
	 * @return bool|string Whether or not to validate seeding.
	 * @see Model::saveAll() See for possible values for `validate`.
	 */
	public function getValidateSeeding() {
		$configKey = 'validate';
		$propertyName = '_validateSeeding';
		$defaultValue = 'first';
		return $this->_getParameter($configKey, $propertyName, $defaultValue);
	}

	/**
	 * Get the seed number for Faker to use
	 *
	 * @return bool|string The seed number for Faker to use
	 * @see Generator::seed Faker's seed method.
	 */
	public function getSeedingNumber() {
		$configKey = 'seed';
		$propertyName = '_seedingNumber';
		$defaultValue = null;
		return $this->_getParameter($configKey, $propertyName, $defaultValue);
	}

	/**
	 * Get whether or not to truncate the model
	 *
	 * @return bool Whether or not to truncate the model
	 */
	public function getNoTruncate() {
		$configKey = 'no-truncate';
		$propertyName = '_noTruncate';
		$defaultValue = false;
		return $this->_getParameter($configKey, $propertyName, $defaultValue);
	}

	/**
	 * Get the value of a parameter
	 *
	 * Inspects
	 * 1. The CLI parameters, e.g. "--records"
	 * 2. The seeder specific configuration, e.g. "FakeSeeder.Article.records"
	 * 3. The general seeder configuration, e.g "FakeSeeder.records"
	 * 4. The seeder shell task class properties, e.g. "$_records"
	 * 4. Falls back to an optional default value
	 *
	 * @param string $configKey The name of the config key to check.
	 * @param string $propertyName The name of the class property to check.
	 * @param string $defaultValue The default value to use as fallback, optional.
	 * @return mixed The value of the parameter.
	 */
	protected function _getParameter($configKey, $propertyName, $defaultValue = null) {
		// If given as CLI parameter, use that value
		if ($this->params[$configKey]) {
			$this->out(__('Parameter "%s" given through CLI parameter: "%s"', $configKey, $this->params[$configKey]), 1, Shell::VERBOSE);
			return $this->params[$configKey];
		}

		// If set in the seeder specific configuration, use that value
		$localeConfigKey = sprintf('%s.%s', $this->_getSeederConfigKey(), $configKey);
		if (Configure::check($localeConfigKey)) {
			$this->out(__('Parameter "%s" configured in seeder specific configuration: "%s"', $configKey, Configure::read($localeConfigKey)), 1, Shell::VERBOSE);
			return Configure::read($localeConfigKey);
		}

		// If set in the general FakeSeeder configuration, use that value
		$localeConfigKey = sprintf('%s.%s', $this->_getSeederShellName(), $configKey);
		if (Configure::check($localeConfigKey)) {
			$this->out(__('Parameter "%s" configured in general seeder configuration: "%s"', $configKey, Configure::read($localeConfigKey)), 1, Shell::VERBOSE);
			return Configure::read($localeConfigKey);
		}

		// If set in the seeder class, use that value
		if ($this->{$propertyName}) {
			$this->out(__('Parameter "%s" set in class: "%s"', $configKey, $this->{$propertyName}), 1, Shell::VERBOSE);
			return $this->{$propertyName};
		}

		$this->out(__('Parameter "%s"  not given/configured, falling back to default "%s".', $configKey, $defaultValue), 1, Shell::VERBOSE);
		// Otherwise use the default value as fallback
		return $defaultValue;
	}

	/**
	 * Get the seeder specific config key
	 *
	 * Can be overridden by setting $_configKey
	 *
	 * @return string The seeder specific config key.
	 * @see ::$_configKey
	 */
	protected function _getSeederConfigKey() {
		$configKey = $this->_getSeederNamePrefix();
		if (!empty($this->_configKey)) {
			$configKey = $this->_configKey;
		}

		return sprintf('%s.%s', $this->_getSeederShellName(), $configKey);
	}

	/**
	 * Get the prefix of the seeder (class) shell task name
	 *
	 * "Article" for "ArticleSeederTask".
	 *
	 * @return string The prefix of the seeder (class) shell task name.
	 */
	protected function _getSeederNamePrefix() {
		$className = get_class($this);
		$seederName = substr($className, 0, -10);
		return $seederName;
	}

	/**
	 * Get the name of the seeder shell
	 *
	 * @return string The name of the seeder shell.
	 * @todo Return actual name of the seeder shell (not task!).
	 */
	protected function _getSeederShellName() {
		return 'FakeSeeder';
	}
}
