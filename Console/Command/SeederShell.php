<?php

App::uses('ShellModelTruncator', 'FakeSeeder.Lib');
App::uses('Folder', 'Utility');

/**
 * SeederShell
 *
 * Can either be used to invoke seeder shell tasks directly
 * or as base class for seeder suites (logically groups of seeders) to base upon.
 *
 * @todo Evaluate if it's sensible to split this class into two (shell and base class)
 */
class SeederShell extends AppShell {

	/**
	 * Defined the seeder tasks names without 'SeederTask' suffix to execute in this suite in in sequential order
	 *
	 * @var array
	 * @todo Consider allowing to set the records per seeder, as sub array
	 */
	protected $_seeders = array();

	/**
	 * Additional models to truncate
	 *
	 * @var array
	 */
	protected $_modelsToTruncate = array();

	/**
	 * Initialize seeder shell, make sure it's OK to seed
	 *
	 * @return void
	 */
	public function initialize() {
		parent::initialize();

		$this->_checkSeedable();
	}

	/**
	 * Check if seeding the database is allowed
	 *
	 * @return void
	 * ¦todo Consider making this over writable by a CLI parameter, e.g. --force or --ignore-check
	 */
	protected function _checkSeedable() {
		if (Configure::read('FakeSeeder.seedable') !== true) {
			$this->_stop(__('Seeding is not activated in configuration in "FakeSeeder.seedable"!'));
		}
		if (Configure::read('debug') < 1) {
			$this->_stop(__('Seeding is allowed only in debug mode!'));
		}
	}

	/**
	 * Get the Console Option Parser
	 *
	 * @return ConsoleOptionParser The Console Option Parser.
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		$parser->description(array(
			__('A shell to seed your database with fake and/or fixed data.'),
			__(''),
			__('Uses Faker to generate the fake data.'),
			__('Uses shell tasks for implementing specific seeders.'),
			__('Organizes logical groups of seeders in custom seeder shells/suites.'),
		));
		$parser->addArguments(array(
			'model' => array(
				'help' => "The name of a seeder shell task without 'SeederTask' suffix.\n" .
					"For example 'Article' for 'ArticleSeederTask'.\n" .
					"Alternatively the name of a model.\n" .
					"It will try to guess the field formatters then.",
				'required' => false,
			)
		));
		$parser->addOptions(array(
			'mode' => array(
				'help' => "The seeding mode.\n" .
					"'manual' = No Field formatters are guessed.\n" .
					"'auto' = All field formatters are guessed.\n" .
					"'mixed' = Only missing field formatters are guessed.\n",
				'short' => 'm',
				'choices' => array('manual', 'auto', 'mixed'),
				'default' => '',
			),
			'locale' => array(
				'help' => 'The locale to use for Faker.',
				'short' => 'l',
				'default' => '',
			),
			'records' => array(
				'help' => 'The amount of records to seed.',
				'short' => 'r',
				'default' => '',
			),
			'validate' => array(
				'help' => 'Whether or not to validate when saving the seeding data.',
				'choices' => array('first', true, false),
				'default' => '',
			),
			'seed' => array(
				'help' => 'Set the seed number for Faker to use.',
				'short' => 's',
				'default' => '',
			),
			'no-truncate' => array(
				'help' => 'Prevents that the model gets truncated before seeding.',
				'boolean' => true,
			),
		));
		$parser->epilog(array(
			__('All shell options can be set through:'),
			__('1. CLI parameter, e.g. "--records"'),
			__('2. The seeder specific configuration, e.g. "FakeSeeder.Article.records"'),
			__('3. The general seeder configuration, e.g "FakeSeeder.records"'),
			__('4. The seeder shell task class properties, e.g. "$_records"'),
			__('The values are checked in that order. The first value found is taken.'),
			__('If no value is set, it will fall back to an optional default value.'),
			__(''),
			__('When no seeders are set (e.g. in a custom seeder suite) and if called without arguments, ' .
				'it will prompt to execute one of the seeder shell tasks available.'),
		));

		return $parser;
	}

	/**
	 * Main Seeder method
	 *
	 * If invoked with a seeder name an argument, it executes this single seeder shell task.
	 * If invoked without seeder name as argument, it prompts for a seeder shell to execute.
	 *
	 * @return void
	 * @todo Consider using DB transaction(s)
	 */
	public function main() {
		// TODO Disable FK constraints

		if (!empty($this->args[0])) {
			// Either execute the given seeder task
			$seederName = $this->args[0];
			$this->out(__('Execute %s seeder...', $seederName));
			$this->_executeSeederTask($seederName, $this->params['no-truncate']);
		} elseif (!empty($this->_seeders)) {
			// Execute all seeders set in $_seeders (only applies for subclasses)
			if ($this->params['no-truncate'] === false) {
				$this->_truncateModels();
			}
			$this->_callSeeders();
		} else {
			// Prompt for a seeder shell task to execute
			$this->_promptForSeeder();
		}

		// TODO Enable FK constraints, if necessary
	}

	/**
	 * Truncate the models
	 *
	 * Merges all the models from all seeders with the additionally configured  models.
	 *
	 * @return void
	 * @see ShellModelTruncator::truncateModels
	 */
	protected function _truncateModels() {
		$this->out(__('Truncating models...'));

		// Get all models from all seeders
		$modelsToTruncate = array();
		foreach ($this->_seeders as $seederName) {
			$modelsToTruncate = array_merge(
				$modelsToTruncate,
				$this->_getModelsToTruncateFromSeederTask($seederName)
			);
		}

		$modelsToTruncate = array_merge($modelsToTruncate, $this->_modelsToTruncate);
		$modelsToTruncate = array_unique($modelsToTruncate);

		$modelTruncator = $this->_getModelTruncator();
		$modelTruncator->truncateModels($modelsToTruncate);

		$this->out(__('Finished truncating models.'));
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
	 * Get the models to truncate from the seeder task
	 *
	 * @param string $seederName The name of the seeder task.
	 * @return array The models to truncate.
	 */
	protected function _getModelsToTruncateFromSeederTask($seederName) {
		$seederName = $seederName . 'Seeder';
		$seederTask = $this->Tasks->load($seederName);

		$models = $seederTask->getModelsToTruncate();
		return $models;
	}

	/**
	 * Call the seeders of the suite
	 *
	 * Does not execute the data truncation in the seeders as we've done that already.
	 *
	 * @return void
	 */
	protected function _callSeeders() {
		$this->out(__('Execute seeders...'));

		foreach ($this->_seeders as $seederName) {
			$this->out(__('Execute %s seeder...', $seederName));
			$this->_executeSeederTask($seederName, true);

		}
		$this->out(__('Finished executing seeders.'));
	}

	/**
	 * Prompts the user with a list of available seeder shell tasks
	 *
	 * Only supports app based seeder shell tasks, at the moment.
	 *
	 * @return void
	 */
	protected function _promptForSeeder() {
		$seederTasks = $this->_getSeederTasks();

		$this->out(__('Choose one seeder shell task to execute:'));
		$taskCount = count($seederTasks);
		for ($i = 0; $i < $taskCount; $i++) {
			$this->out(sprintf("%d. %s", $i + 1, $seederTasks[$i]));
		}

		$chosenSeeder = $this->in(__("Enter a number from the list above,\n" .
			"type in the name of another seeder shell task,\n" .
			"type in the name of a model,\n" .
			"or 'q' to exit"), null, 'q');

		if ($chosenSeeder === 'q') {
			$this->_stop();
			return;
		}

		if (!$chosenSeeder || (int)$chosenSeeder > $taskCount) {
			$this->err(__d('cake_console', "The seeder shell task name you supplied was empty,\n" .
				"or the number you selected was not a valid option. Please try again."));
			$this->_stop();
			return;
		}
		if ((int)$chosenSeeder > 0 && (int)$chosenSeeder <= $taskCount) {
			$chosenSeeder = $seederTasks[(int)$chosenSeeder - 1];
		}

		$this->out(__('Execute %s seeder...', $chosenSeeder));
		// Add seeder to argument list, in case it is a model
		$this->args[0] = $chosenSeeder;
		$this->_executeSeederTask($chosenSeeder, $this->params['no-truncate']);
	}

	/**
	 * Get the available seeder shell tasks
	 *
	 * Checks for *SeederTask.php files in
	 * app/Console/Command/Task/.
	 *
	 * @return array The available seeder shell tasks.
	 * @todo Also support plugin shell tasks.
	 * @todo Improve testability by getting the Folder object from externally.
	 */
	protected function _getSeederTasks() {
		$taskDir = ROOT . DS . APP_DIR . DS . 'Console' . DS . 'Command' . DS . 'Task' . DS;
		$dir = new Folder($taskDir);
		$files = $dir->find('(.*)SeederTask\.php');
		$seedTasks = array();
		foreach ($files as $file) {
			$seedTasks[] = substr(basename($file), 0, -14);
		}
		sort($seedTasks);
		return $seedTasks;
	}

	/**
	 * Execute a seeder Task
	 *
	 * Loads a task and make sure it is initialized properly
	 *
	 * @param string $seederName The name of the seeder task, without SeederTask.
	 * @param bool $noTruncate Prevents that the model gets truncated before seeding, defaults to true.
	 * @return void
	 */
	protected function _executeSeederTask($seederName, $noTruncate = true) {
		$seederNameSuffixed = $seederName . 'Seeder';
		try {
			$seederTask = $this->Tasks->load($seederNameSuffixed);
		} catch (MissingTaskException $e) {
			$this->out(__(
				"No seeder shell tasks named '%s' found. Trying to find a '%s' model.",
				$seederNameSuffixed,
				$seederName
			));

			// Make sure the table/model exists
			$model = $this->_loadSeederModel($seederName);
			if ($model === false) {
				$this->out(__("No model '%s' found , aborting.", $seederName));
				return;
			}

			// Execute the DynamicModelSeeder if the model exists
			$seederTask = $this->Tasks->load('FakeSeeder.DynamicModelSeeder');
		}
		// Copy given arguments & parameters
		$seederTask->args =& $this->args;
		$seederTask->params =& $this->params;
		// Overwrite no-truncate to make ake sure a task does not truncates again
		// when executed as a site
		$seederTask->params['no-truncate'] = $noTruncate;

		$seederTask->initialize();

		$seederTask->execute();
	}

	/**
	 * Load a model to seed
	 *
	 * @param string $seederName The model to load
	 * @return
	 */
	protected function _loadSeederModel($seederName) {
		return ClassRegistry::init($seederName);
	}
}
