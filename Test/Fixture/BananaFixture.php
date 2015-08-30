<?php

class BananaFixture extends CakeTestFixture {

	public $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'title' => array('type' => 'string', 'length' => 255, 'null' => false),
		'body' => 'text',
		'published' => array('type' => 'integer', 'default' => '0', 'null' => false),
		'created' => 'datetime',
		'updated' => 'datetime'
	);

	public function init() {
		$this->records = array(
			array(
				'id' => 1,
				'title' => 'First Article',
				'body' => 'First Article Body',
				'published' => '1',
				'created' => date('Y-m-d H:i:s'),
				'updated' => date('Y-m-d H:i:s'),
			),
		);
		parent::init();
	}
}