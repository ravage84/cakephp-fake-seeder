<?php

App::uses('SeederTaskBase', 'FakeSeeder.Console');

/**
 * A Dynamic Model Seeder Task
 *
 * Gets called when there is no seeder shell task named like given one.
 * Since there are no field formatters set, it needs to guess them.
 * Most often, this only works for very simple models that have no validation rules.
 * So don't expect any great magic done here!
 */
class DynamicModelSeederTask extends SeederTaskBase {

	/**
	 * Force the seeder to guess the field formaters
	 *
	 * @return mixed The the 'auto' seeding mode.
	 */
	public function getSeedingMode() {
		return 'auto';
	}

	/**
	 * Take the model name from the argument list
	 *
	 * @return string The model name, taken from the argument list.
	 */
	public function getModelName() {
		$modelName = $this->args[0];

		return $modelName;
	}

	/**
	 * Set/get the field formatters
	 *
	 * Just return the field formatters.
	 *
	 * {@inheritDoc}
	 */
	public function fieldFormatters() {
		return $this->_fieldFormatters;
	}

}
