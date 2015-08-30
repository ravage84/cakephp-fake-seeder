<?php

App::uses('ColumnTypeGuesser', 'FakeSeeder.Lib');

/**
 * Shell Seed Processor
 */
class ShellSeedProcessor {

	/**
	 * Aseeder shell task instance
	 *
	 * @var null|SeederTaskBase
	 */
	protected $_seeder = null;

	/**
	 * ModelTruncator constructor
	 *
	 * @param Shell $seeder A seeder shell task
	 */
	public function __construct($seeder) {
		$this->_seeder = $seeder;
	}

	/**
	 * Process the fixtures
	 *
	 * @return void
	 */
	public function processFixtures() {
		$fixtures = $this->_seeder->fixtureRecords();
		if (empty($fixtures)) {
			return;
		}
		$this->saveSeeds($fixtures);
	}

	/**
	 * Sow the seeds
	 *
	 * @return void
	 */
	public function sowSeeds() {
		$modelName = $this->_seeder->getModelName();
		$recordsTotal = $this->_seeder->getRecordsCount();
		$this->_seeder->out(__('Sowing %s seeds for model %s', $recordsTotal, $modelName));

		$fieldFormatters = $this->getFieldFormatters();
		if (empty($fieldFormatters)) {
			$this->_seeder->out(__('No field formatters configured, aborting.'));
			return;
		}

		// Improve seed access
		$this->_seeder->seeds = array();
		for ($record = 1; $record <= $recordsTotal; $record++) {
			$newSeed = $this->createSeed($fieldFormatters);
			if (empty($newSeed)) {
				$this->_seeder->out(__('Created seed is empty! Check the field formatters.'));
				break;
			}
			$this->_seeder->seeds[] = $newSeed;

			$this->_seeder->out('.', 0);
			$modulo = $record % 50;
			if ($modulo == 0 || $record == $recordsTotal) {
				$alignSpaces = 0;
				if ($modulo !== 0 && $record >= 50) {
					$alignSpaces = 50 - $modulo;
				}
				$alignSpaces = $alignSpaces + strlen($recordsTotal) - strlen($record) + 1;
				$alignSpaces = str_repeat(' ', $alignSpaces);
				$this->_seeder->out(sprintf('%s%s / %s', $alignSpaces, $record, $recordsTotal));
			}
		}
		$this->saveSeeds($this->_seeder->seeds);

		$this->_seeder->out(__('Finished sowing %s seeds for model %s', $recordsTotal, $modelName));
	}

	/**
	 * Get the field formatters depending on the seeding mode
	 *
	 * 'manual' = No Field formatters are guessed.
	 * 'auto' = All field formatters are guessed.
	 * 'mixed' = Only missing field formatters are guessed.
	 *
	 * @return array The field formatters
	 */
	public function getFieldFormatters() {
		$mode = $this->_seeder->getSeedingMode();
		switch ($mode) {
			case 'manual':
				return $this->_seeder->fieldFormatters();
			case 'auto':
				return $this->_guessFieldFormatters();
			case 'mixed':
				// TODO Improve by only guessing those needed
				$guesedFormatters = $this->_guessFieldFormatters();
				$setFormatters = $this->_seeder->fieldFormatters();
				return array_merge(
					$guesedFormatters,
					$setFormatters
				);
		}
		// TODO Handle invalid mode
		return array();
	}

	/**
	 * Guess the field formatters based on the column type
	 *
	 * @return array The guessed field formatters.
	 */
	protected function _guessFieldFormatters() {
		$columns = $this->_getColumns();

		$columnTypeGuesser = $this->_getColumnTypeGuesser();
		$fieldFormatters = array();
		foreach ($columns as $columnName => $column) {
			$fieldFormatters[$columnName] = $columnTypeGuesser->guessFormat($column);
		}
		return $fieldFormatters;
	}

	/**
	 * Get a ColumnTypeGuesser instance
	 *
	 * @return ColumnTypeGuesser A ColumnTypeGuesser instance.
	 */
	protected function _getColumnTypeGuesser() {
		return new ColumnTypeGuesser($this->_seeder->faker);
	}

	/**
	 * Get the columns (schema)
	 *
	 * Removes the primary key, though.
	 *
	 * @return array The columns (schema).
	 */
	protected function _getColumns() {
		$model = $this->_getModel();
		$columns = $model->schema();
		unset($columns[$model->primaryKey]);
		return $columns;
	}

	/**
	 * Create seed to sow
	 *
	 * Gets some (optional) record state data,
	 * which can be shared by all field formatters of one record.
	 *
	 * @param array $fieldFormatters The fields and their formatter
	 * @return array A seed to sow.
	 */
	public function createSeed($fieldFormatters) {
		$seed = array();
		$modelName = $this->_seeder->getModelName();
		$state = $this->_seeder->recordState();

		foreach ($fieldFormatters as $fieldName => $formatter) {
			$seed[$modelName][$fieldName] = $formatter($state);
		}
		return $seed;
	}

	/**
	 * Save the seeds
	 *
	 * @param array $seeds The seeds to save.
	 * @return void
	 * @todo Make data saving/validation handling configurable (atomic true/false)
	 */
	public function saveSeeds($seeds) {
		$model = $this->_getModel();
		$validate = $this->_seeder->getValidateSeeding();

		if ($validate === 'false') {
			$validate = false;
		} elseif ($validate !== 'first' && $validate !== 'only') {
			$validate = (bool)$validate;
		}

		$saved = $model->saveAll(
			$seeds,
			array(
				'validate' => $validate
			)
		);
		if (!$saved) {
			$this->_seeder->out(__('Seeds could not be saved successfully!'));
			$this->_seeder->out(__('Data validation errors: %s', var_export($model->validationErrors, true)), 1, Shell::VERBOSE);
		}
	}

	/**
	 * Get the model instance
	 *
	 * @return Model The model instance.
	 */
	protected function _getModel() {
		$modelName = $this->_seeder->getModelName();
		$model = ClassRegistry::init($modelName);
		return $model;
	}
}