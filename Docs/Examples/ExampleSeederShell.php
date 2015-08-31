<?php

App::uses('SeederShell', 'FakeSeeder.Console/Command');

/**
 * Example Seeder Suite
 *
 * Can be used to create custom suites of seeders, to logically group them.
 * Must be placed in app/Console/Command.
 * Can be invoked by executing ``php Console/cake.php ExampleSeeder``.
 * Allows to use all the FakeSeeder console arguments and options.
 */
class ExampleSeederShell extends SeederShell {

	/**
	 * Seeders to call in this suite, names without 'SeederShell' suffix
	 *
	 * @var array
	 */
	protected $_seeders = array(
		'ModelA',
		'ModelB',
		'ModelC',
	);
}
