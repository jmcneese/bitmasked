<?php

App::uses('CakeTestCase', 'TestSuite');
App::uses('CakeTestModel', 'TestSuite/Fixture');
App::uses('BitmaskedBit', 'Bitmasked.Model');
App::uses('Bitmaskedt', 'Bitmasked.Model/Behavior');

/**
 * BitmaskedThing
 *
 * @uses CakeTestModel
 */
class BitmaskedThing extends CakeTestModel {

	/**
	 * Behaviors
	 *
	 * @var array
	 */
	public $actsAs = array(
		'Bitmasked.Bitmasked' => array(
			'bits' => array(
				'ALL' => 1,
				'REGISTERED' => 2,
				'18PLUS' => 4,
				'21PLUS' => 8
			)
		)
	);

	/**
	 * getMask method
	 *
	 * Helper method for custom mask functions
	 *
	 * @return integer
	 */
	public function getMask() {
		return 2;
	}

}

/**
 * getCustomMask function
 *
 * Custom loose function for callback test
 *
 * @return integer
 */
function getCustomMask() {
	return 4;
}

/**
 * BitmaskedBehaviorTest
 *
 * @package		Bitmasked
 * @subpackage	Bitmasked.Test.Case.Model.Behavior
 * @author		Joshua McNeese <jmcneese@gmail.com>
 * @license		Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * @copyright	Copyright (c) 2009-2012 Joshua M. McNeese, Curtis J. Beeson
 * @see		    BitmaskedBehavior
 * @property    BitmaskedThing  BitmaskedThing
 * @property    BitmaskedBit    BitmaskedBit
 * @uses	    CakeTestCase
 */
class BitmaskedBehaviorTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = array(
		'plugin.bitmasked.bitmasked_thing',
		'plugin.bitmasked.bitmasked_bit'
	);

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->BitmaskedThing = ClassRegistry::init('Bitmasked.BitmaskedThing');
		$this->BitmaskedBit = ClassRegistry::init('Bitmasked.BitmaskedBit');
	}

	/**
	 * Test Setup
	 *
	 * @return void
	 */
	public function testSetup() {
		$this->BitmaskedThing->Behaviors->unload('Bitmasked');
		$this->BitmaskedThing->Behaviors->load('Bitmasked', array(
			'bits' => array()
		));
		$this->assertTrue($this->BitmaskedThing->Behaviors->Bitmasked->settings['BitmaskedThing']['disabled']);
		$this->BitmaskedThing->Behaviors->unload('Bitmasked');
		$this->BitmaskedThing->Behaviors->load('Bitmasked', array(
			'bits' => array(
				'FOO',
				'BAR'
			)
		));
		$this->assertEquals($this->BitmaskedThing->Behaviors->Bitmasked->settings['BitmaskedThing']['bits'], array(
			'ALL' => 1,
			'FOO' => 2,
			'BAR' => 4
		));
	}

	/**
	 * Test getBitmaskedBitAlias
	 *
	 * @return void
	 */
	public function testGetBitmaskedBitAlias() {
		$this->assertEquals(
			$this->BitmaskedThing->getBitmaskedBitAlias(),
			$this->BitmaskedThing->alias . 'BitmaskedBit'
		);
	}

	/**
	 * Test isBitmaskedDisabled
	 *
	 * @return void
	 */
	public function testIsBitmaskedDisabled() {
		$this->assertFalse($this->BitmaskedThing->isBitmaskedDisabled());
	}

	/**
	 * Test disableBitmasked
	 *
	 * @return void
	 */
	public function testDisableBitmasked() {
		$this->BitmaskedThing->disableBitmasked();
		$this->assertTrue($result = $this->BitmaskedThing->isBitmaskedDisabled());
		$this->BitmaskedThing->disableBitmasked(false);
		$this->assertFalse($this->BitmaskedThing->isBitmaskedDisabled());
	}

	/**
	 * Test getBitmaskedBit
	 *
	 * @return void
	 */
	public function testGetBitmaskedBit() {
		$id = 4;
		// test with missing id
		$this->assertEmpty($this->BitmaskedThing->getBitmaskedBit());
		// test with bad id
		$this->assertEmpty($this->BitmaskedThing->getBitmaskedBit(999));
		// test with explicit id
		$result = $this->BitmaskedThing->getBitmaskedBit($id);
		$this->assertTrue(Set::matches("/BitmaskedThingBitmaskedBit[foreign_id={$id}]", $result));
		// test with implicit id
		$this->BitmaskedThing->id = $id;
		$result = $this->BitmaskedThing->getBitmaskedBit();
		$this->assertTrue(Set::matches("/BitmaskedThingBitmaskedBit[foreign_id={$id}]", $result));
	}

	/**
	 * Test deleteBitmaskedBit
	 *
	 * @return void
	 */
	public function testDeleteBitmaskedBit() {
		$id = 4;
		// test with missing id
		$this->assertFalse($this->BitmaskedThing->deleteBitmaskedBit());
		// test with explicit id
		$this->assertTrue($this->BitmaskedThing->deleteBitmaskedBit($id));
		$this->assertEmpty($this->BitmaskedThing->getBitmaskedBit($id));
		// test with implicit id
		$this->BitmaskedThing->id = $id;
		$this->assertTrue($this->BitmaskedThing->deleteBitmaskedBit());
		$this->assertEmpty($this->BitmaskedThing->getBitmaskedBit($id));
	}

	/**
	 * Test getBits
	 *
	 * @return void
	 */
	public function testGetBits() {
		$id = 1;
		// test with missing id
		$this->assertFalse($this->BitmaskedThing->getBits());
		// test with bad id
		$this->assertFalse($this->BitmaskedThing->getBits(999));
		// test with explicit id
		$this->assertEquals(1, $this->BitmaskedThing->getBits($id) & 1);
		// test with implicit id
		$this->BitmaskedThing->id = $id;
		$this->assertEquals(1, $this->BitmaskedThing->getBits() & 1);
	}

	/**
	 * Test afterSave (create)
	 *
	 * @return void
	 */
	public function testAfterSaveCreate() {
		$this->BitmaskedThing->create();
		$bits = 12;
		$alias = $this->BitmaskedThing->getBitmaskedBitAlias();
		// test disabled
		$this->BitmaskedThing->disableBitmasked();
		$this->BitmaskedThing->save(array(
			'BitmaskedThing' => array(
				'name' => 'Geegaw',
				'desc' => 'A Geegaw is a type of Thing'
			),
			$alias => array(
				'bits' => $bits
			)
		));
		$this->assertFalse($this->BitmaskedThing->getBits());
		// test enabled
		$this->BitmaskedThing->disableBitmasked(false);
		$this->BitmaskedThing->create();
		$this->BitmaskedThing->save(array(
			'BitmaskedThing' => array(
				'name' => 'FrooFroo',
				'desc' => 'A FrooFroo is a type of Thing'
			),
			$alias => array(
				'bits' => $bits
			)
		));
		$this->assertEquals($bits, $this->BitmaskedThing->getBits());
		// test alternate data formats
		$this->BitmaskedThing->create();
		$this->BitmaskedThing->save(array(
			'BitmaskedThing' => array(
				'name' => 'ShoopShoop',
				'desc' => 'A ShoopShoop is a type of Thing',
				'bits' => $bits
			)
		));
		$this->assertEquals($bits, $this->BitmaskedThing->getBits());
		$this->BitmaskedThing->create();
		$this->BitmaskedThing->save(array(
			'BitmaskedThing' => array(
				'name' => 'DooWop',
				'desc' => 'A DooWop is a type of Thing'
			),
			'BitmaskedBit' => array(
				'id' => 1234,
				'bits' => $bits
			)
		));
		$this->assertEquals($bits, $this->BitmaskedThing->getBits());
		$this->BitmaskedThing->create();
		$this->BitmaskedThing->save(array(
			'BitmaskedThing' => array(
				'name' => 'ShooWop',
				'desc' => 'A ShooWop is a type of Thing'
			),
			'BitmaskedBit' => array(
				'bits' => array(
					'ALL',
					'REGISTERED'
				)
			)
		));
		$this->assertEquals(3, $this->BitmaskedThing->getBits());
		// test bad data
		$this->BitmaskedThing->create();
		$this->BitmaskedThing->save(array(
			'BitmaskedThing' => array(
				'name' => 'EepEep',
				'desc' => 'A EepEep is a type of Thing'
			),
			'BitmaskedBit' => array(
				'model' => '',
				'foreign_id' => '',
				'bits' => 'xxx'
			)
		));
		$this->assertFalse($this->BitmaskedThing->getBits());
	}

	/**
	 * test updating existing record w/out providing bits
	 * test due to bug: behavior should not reset bits to default
	 *
	 * @author	Ryan Morris <ryan@houseparty.com>
	 * @return 	void
	 */
	public function testUpdateWithoutBits() {
		// first lets set up a thing and give it some non-default bits
		$this->BitmaskedThing->id = 1;
		$newBits = 12;
		$oldBits = $this->BitmaskedThing->getBits();
		$alias = $this->BitmaskedThing->getBitmaskedBitAlias();
		$this->BitmaskedThing->save(array(
			$alias => array(
				'bits' => $newBits
			)
		));
		$resultingBits = $this->BitmaskedThing->getBits();
		$this->assertEquals($newBits, $resultingBits);
		$this->assertNotEquals($oldBits, $resultingBits);
		// now lets test to ensure updating the Thing will not impact its bits
		$this->BitmaskedThing->create(null);
		$this->BitmaskedThing->save(array(
			'id' => 1,
			'name' => 'newname'
		));
		$this->BitmaskedThing->id = 1;
		$bits = $this->BitmaskedThing->getBits();
		$this->assertEquals($resultingBits, $bits, "Bits should NOT have changed after the update");
	}

	/**
	 * Test afterSave (update)
	 *
	 * @return void
	 */
	public function testAfterSaveUpdate() {
		$this->BitmaskedThing->id = 1;
		$newBits = 12;
		$oldBits = $this->BitmaskedThing->getBits();
		$alias = $this->BitmaskedThing->getBitmaskedBitAlias();
		$this->BitmaskedThing->save(array(
			$alias => array(
				'bits' => $newBits
			)
		));
		$result = $this->BitmaskedThing->getBits();
		$this->assertEquals($newBits, $result);
		$this->assertNotEquals($oldBits, $result);
		$this->BitmaskedThing->create(null);
		$newBits = 11;
		$oldBits = $this->BitmaskedThing->getBits();
		$alias = $this->BitmaskedThing->getBitmaskedBitAlias();
		$this->BitmaskedThing->save(array(
			$alias => array(
				'id' => 1,
				'bits' => $newBits
			)
		));
		$result = $this->BitmaskedThing->getBits();
		$this->assertEquals($newBits, $result);
		$this->assertNotEquals($oldBits, $result);
		$result = ClassRegistry::init('Bitmasked.BitmaskedBit')->find('all', array(
			'conditions' => array(
				'BitmaskedBit.foreign_id' => 1,
				'BitmaskedBit.model' => 'BitmaskedThing'
			)
		));
		$this->assertCount(1, $result);
	}

	/**
	 * Test beforeDelete
	 *
	 * @return void
	 */
	public function testBeforeDelete() {
		// test enabled
		$id = 1;
		$this->BitmaskedThing->delete($id);
		$result = $this->BitmaskedBit->find('count', array(
			'conditions' => array(
				"BitmaskedBit.model" => 'BitmaskedThing',
				"BitmaskedBit.foreign_id" => $id
			)
		));
		$this->assertEquals(0, $result);
		// test disabled
		$this->BitmaskedThing->disableBitmasked();
		$id = 2;
		$this->BitmaskedThing->delete($id);
		$result = $this->BitmaskedBit->find('count', array(
			'conditions' => array(
				"BitmaskedBit.model" => 'BitmaskedThing',
				"BitmaskedBit.foreign_id" => $id
			)
		));
		$this->assertGreaterThan(0, $result);
	}

	/**
	 * Test beforeFind
	 *
	 * @return void
	 */
	public function testBeforeFind() {
		$alias = $this->BitmaskedThing->getBitmaskedBitAlias();
		// test disabled
		$this->BitmaskedThing->disableBitmasked();
		$result = $this->BitmaskedThing->find('all', array(
			'bitmask' => 1
		));
		$this->assertEmpty(Set::extract("/{$alias}/.", $result));
		$this->assertCount(4, $result);
		// test enabled
		$this->BitmaskedThing->disableBitmasked(false);
		// test with top-level query option
		$result1 = $this->BitmaskedThing->find('all', array(
			'bitmask' => 1
		));
		$this->assertTrue(Set::matches("/{$alias}[bits=1]", $result1));
		$this->assertCount(1, $result1);
		// test with condition-level query option
		$result2 = $this->BitmaskedThing->find('all', array(
			'conditions' => array(
				'bitmask' => 1
			)
		));
		$this->assertEquals($result1, $result2);
		if (floatval(PHP_VERSION) >= 5.3) {
			// test closure bitmask callback
			$result = $this->BitmaskedThing->find('all', array(
				'bitmask' => function() {
					return 12;
				}
			));
			$this->assertTrue(Set::matches("/{$alias}[bits=12]", $result));
			$this->assertCount(2, $result);
		}
		// test model method bitmask callback
		$result = $this->BitmaskedThing->find('all', array(
			'bitmask' => 'getMask'
		));
		$this->assertTrue(Set::matches("/{$alias}[bits=2]", $result));
		$this->assertCount(1, $result);
		// test global function bitmask callback
		$result = $this->BitmaskedThing->find('all', array(
			'bitmask' => 'getCustomMask'
		));
		$this->assertTrue(Set::matches("/{$alias}[bits=4]", $result));
		$this->assertCount(2, $result);
		// test flag (string) bitmask
		$result = $this->BitmaskedThing->find('all', array(
			'bitmask' => '18PLUS'
		));
		$this->assertTrue(Set::matches("/{$alias}[bits=4]", $result));
		$this->assertCount(2, $result);
		// test flags (array) bitmask
		$result = $this->BitmaskedThing->find('all', array(
			'bitmask' => array(
				'ALL',
				'21PLUS'
			)
		));
		$this->assertTrue(Set::matches("/{$alias}[bits=12]", $result));
		$this->assertCount(2, $result);
	}

	/**
	 * ensure beforeFind supports inverse bit queries via 'bitmask' key
	 *
	 * @return	void
	 */
	public function testBeforeFindAllowsFilteringByInverseBit() {

		$alias = $this->BitmaskedThing->getBitmaskedBitAlias();
		$result1 = $this->BitmaskedThing->find('all', array(
			'bitmask' => '~1'
		));
		$this->assertEmpty(Set::matches("/{$alias}[bits=1]", $result1), "Expect no results with bitmask value of 1");

	}

	/**
	 * Test hasBit
	 *
	 * @return void
	 */
	public function testHasBit() {
		$id = 1;
		$bit = 1;
		// test with missing id
		$this->assertFalse($this->BitmaskedThing->hasBit());
		// test with bad id
		$this->assertFalse($this->BitmaskedThing->hasBit(999, $bit));
		// test with explicit id
		$this->assertTrue($this->BitmaskedThing->hasBit($id, $bit));
		// test with implicit id
		$this->BitmaskedThing->id = $id;
		$this->assertTrue($this->BitmaskedThing->hasBit(null, $bit));
		// test with string bit
		$this->assertTrue($this->BitmaskedThing->hasBit($id, 'ALL'));
		// test with bad string bit
		$this->assertFalse($this->BitmaskedThing->hasBit($id, 'SHOOP'));
		// test with a missing bit
		$alias = $this->BitmaskedThing->getBitmaskedBitAlias();
		$this->BitmaskedThing->create();
		$this->BitmaskedThing->save(array(
			'BitmaskedThing' => array(
				'name' => 'Geegaw',
				'desc' => 'A Geegaw is a type of Thing'
			),
			$alias => array(
				'bits' => array(
					'ALL',
					'REGISTERED',
					'18PLUS'
				)
			)
		));
		$this->assertFalse($this->BitmaskedThing->hasBit(null, '21PLUS'));
	}

}