<?php

App::uses('CakeTestFixture', 'TestSuite/Fixture');

/**
 * BitmaskedBitFixture
 *
 * @package		Bitmasked
 * @subpackage	Bitmasked.Test.Fixture
 * @author		Joshua McNeese <jmcneese@gmail.com>
 * @license		Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * @copyright	Copyright (c) 2009-2012 Joshua M. McNeese, Curtis J. Beeson
 * @uses 		CakeTestFixture
 */
class BitmaskedBitFixture extends CakeTestFixture {

	/**
	 * Fields
	 *
	 * @var array
	 */
	public $fields = array(
		'id' => array(
			'type' => 'integer',
			'null' => false,
			'default' => NULL,
			'key' => 'primary'
		),
		'model' => array(
			'type' => 'string',
			'null' => false,
			'default' => NULL,
			'length' => 32,
			'key' => 'index'
		),
		'foreign_id' => array(
			'type' => 'integer',
			'null' => false,
			'default' => NULL
		),
		'bits' => array(
			'type' => 'integer',
			'null' => false,
			'default' => '1',
			'length' => 20
		),
		'indexes' => array(
			'PRIMARY' => array(
				'column' => 'id',
				'unique' => 1
			),
			'polymorphic_idx' => array(
				'column' => array(
					'model',
					'foreign_id'
				),
				'unique' => 0
			)
		)
	);

	/**
	 * Records
	 *
	 * @var array
	 */
	public $records = array(
		array(
			'id' => 1,
			'model' => 'BitmaskedThing',
			'foreign_id' => 1,
			'bits' => 1
		),
		array(
			'id' => 2,
			'model' => 'BitmaskedThing',
			'foreign_id' => 2,
			'bits' => 2
		),
		array(
			'id' => 3,
			'model' => 'BitmaskedThing',
			'foreign_id' => 3,
			'bits' => 4
		),
		array(
			'id' => 4,
			'model' => 'BitmaskedThing',
			'foreign_id' => 4,
			'bits' => 12
		)
	);

}
