<?php
/**
 * All FakeSeeder plugin tests
 */
class AllFakeSeederTest extends CakeTestCase {

	/**
	 * Suite define the tests for this plugin
	 *
	 * @return void
	 */
	public static function suite() {
		$suite = new CakeTestSuite('All FakeSeeder test');

		$path = CakePlugin::path('FakeSeeder') . 'Test' . DS . 'Case' . DS;
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}

}
