<?php

/**
 * @package		bitmasked
 * @subpackage	bitmasked.models
 * @author		Joshua McNeese <jmcneese@gmail.com>
 * @license		Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * @copyright	Copyright (c) 2009-2011 Joshua M. McNeese, Curtis J. Beeson
 */

/**
 * BitmaskedBit Model Test Case
 *
 * @see		BitmaskedBit
 * @uses	CakeTestCase
 */
class BitmaskedBitTestCase extends CakeTestCase {

	/**
	 * @var array
	 */
	public $fixtures = array(
		'plugin.bitmasked.bitmasked_bit'
	);

	/**
	 * @return void
	 */
	public function start() {
		parent::start();
		$this->BitmaskedBit = ClassRegistry::init('Bitmasked.BitmaskedBit');
	}

	/**
	 * Test Instance Creation
	 *
	 * @return void
	 */
	public function testInstanceSetup() {
		$this->assertIsA($this->BitmaskedBit, 'Model');
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

?>