<?php

/**
 * Shell Model Truncator
 */
class ShellModelTruncator {

	/**
	 * The shell or shel task object for interacting with the console
	 *
	 * @var null|Shell
	 */
	protected $_shell = null;

	/**
	 * ModelTruncator constructor
	 *
	 * @param Shell $shell A shell or shell task object.
	 */
	public function __construct($shell) {
		$this->_shell = $shell;
	}

	/**
	 * Truncate the given models
	 *
	 * @param array $modelsToTruncate An array of models (names) to truncate
	 * @return void
	 * @todo Improve testability by extracting the model object retrieval part.
	 */
	public function truncateModels($modelsToTruncate) {
		foreach ($modelsToTruncate as $modelName) {
			$this->_shell->out(__('Truncate model %s...', $modelName), 1, Shell::VERBOSE);
			$model = ClassRegistry::init($modelName);
			$datasource = $model->getDataSource();
			$datasource->truncate($model->table);
		}
	}
}