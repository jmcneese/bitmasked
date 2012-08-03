<?php

/**
 * @package		bitmasked
 * @subpackage	bitmasked.test.case.model
 * @author		Joshua McNeese <jmcneese@gmail.com>
 * @license		Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * @copyright	Copyright (c) 2009-2012 Joshua M. McNeese, Curtis J. Beeson
 */

/**
 * BitmaskedBitTest
 *
 * @see		BitmaskedBit
 * @uses	CakeTestCase
 */
class BitmaskedBitTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = array(
		'plugin.bitmasked.bitmasked_bit'
	);

	/**
	 * setUp Method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->BitmaskedBit = ClassRegistry::init('Bitmasked.BitmaskedBit');
	}

	/**
	 * Test Validation
	 *
	 * @return void
	 */
	public function testValidation() {
		$this->BitmaskedBit->create();
		$data = array(
			'model' => null,
			'foreign_id' => null,
			'bits' => 'x'
		);
		$result = $this->BitmaskedBit->save($data);
		$this->assertFalse($result);
		$this->assertEqual(count($this->BitmaskedBit->invalidFields()), count($data));
	}

}