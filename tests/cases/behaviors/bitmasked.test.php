<?php

/**
 * @package     bitmasked
 * @subpackage  bitmasked.tests.cases.behaviors
 * @author      Joshua McNeese <jmcneese@gmail.com>
 * @license		Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * @copyright	Copyright (c) 2009-2011 Joshua M. McNeese, Curtis J. Beeson
 */

/**
 * Bitmasked Thing Model
 *
 * @uses CakeTestModel
 */
class BitmaskedThing extends CakeTestModel {

    public $actsAs = array(
        'Bitmasked.Bitmasked' => array(
	        'bits' => array(
				'ALL'       => 1,
				'REGISTERED'=> 2,
				'18PLUS'    => 4,
				'21PLUS'    => 8
			)
        )
    );

	/**
	 * Helper method for custom mask functions
	 *
	 * @return integer
	 */
	public function getMask() {

		return 2;

	}

}

/**
 * Custom loose function for callback test
 *
 * @return integer
 */
function getCustomMask() {

	return 4;

}

/**
 * Bitmasked Test Case
 *
 * @see     BitmaskedBehavior
 * @uses    CakeTestCase
 */
class BitmaskedTestCase extends CakeTestCase {

    /**
     * @var array
     */
    public $fixtures = array(
        'plugin.bitmasked.bitmasked_thing',
        'plugin.bitmasked.bitmasked_bit'
    );

    /**
     * @return void
     */
    public function startTest($method = null) {

        parent::startTest($method);

        $this->BitmaskedThing = ClassRegistry::init('Bitmasked.BitmaskedThing');

    }

	/**
     * @return void
     */
    public function endTest($method = null) {

        parent::endTest($method);

        ClassRegistry::removeObject('BitmaskedThing');

    }

    /**
     * Test Instance Creation
     *
     * @return void
     */
    public function testSetup() {

        $this->assertIsA($this->BitmaskedThing, 'Model');
        $this->assertTrue($this->BitmaskedThing->Behaviors->attached('Bitmasked'));

	    $this->BitmaskedThing->Behaviors->detach('Bitmasked');
	    $this->BitmaskedThing->Behaviors->attach('Bitmasked', array(
		    'bits' => array()
	    ));
		$this->assertTrue($this->BitmaskedThing->Behaviors->Bitmasked->settings['BitmaskedThing']['disabled']);

	    $this->BitmaskedThing->Behaviors->detach('Bitmasked');
	    $this->BitmaskedThing->Behaviors->attach('Bitmasked', array(
		    'bits' => array(
				'FOO',
				'BAR'
			)
	    ));
		$this->assertEqual($this->BitmaskedThing->Behaviors->Bitmasked->settings['BitmaskedThing']['bits'], array(
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

	    $result = $this->BitmaskedThing->getBitmaskedBitAlias();
	    $this->assertEqual($result, $this->BitmaskedThing->alias.'BitmaskedBit');

    }

	/**
     * Test isBitmaskedDisabled
     *
     * @return void
     */
    public function testIsBitmaskedDisabled() {

	    $result = $this->BitmaskedThing->isBitmaskedDisabled();
	    $this->assertFalse($result);

    }

	/**
     * Test disableBitmasked
     *
     * @return void
     */
    public function testDisableBitmasked() {

	    $this->BitmaskedThing->disableBitmasked();
	    $result = $this->BitmaskedThing->isBitmaskedDisabled();
	    $this->assertTrue($result);

	    $this->BitmaskedThing->disableBitmasked(false);
	    $result = $this->BitmaskedThing->isBitmaskedDisabled();
	    $this->assertFalse($result);

    }

    /**
     * Test getBitmaskedBit
     *
     * @return void
     */
    public function testGetBitmaskedBit() {

	    $id = 4;

	    // test with missing id
	    $result = $this->BitmaskedThing->getBitmaskedBit();
	    $this->assertFalse($result);

	    // test with bad id
	    $result = $this->BitmaskedThing->getBitmaskedBit(999);
	    $this->assertFalse($result);

	    // test with explicit id
	    $result = $this->BitmaskedThing->getBitmaskedBit($id);
	    $this->assertTrue($result);
	    $this->assertTrue(Set::matches("/BitmaskedThingBitmaskedBit[foreign_id={$id}]", $result));

	    // test with implicit id
	    $this->BitmaskedThing->id = $id;
	    $result = $this->BitmaskedThing->getBitmaskedBit();
	    $this->assertTrue($result);
	    $this->assertTrue(Set::matches("/BitmaskedThingBitmaskedBit[foreign_id={$id}]", $result));

    }

	/**
     * Test getBits
     *
     * @return void
     */
    public function testGetBits() {

	    $id = 1;

	    // test with missing id
	    $result = $this->BitmaskedThing->getBits();
	    $this->assertFalse($result);

	    // test with bad id
	    $result = $this->BitmaskedThing->getBits(999);
	    $this->assertFalse($result);

	    // test with explicit id
	    $result = $this->BitmaskedThing->getBits($id);
	    $this->assertTrue($result);
	    $this->assertTrue($result & 1);

	    // test with implicit id
	    $this->BitmaskedThing->id = $id;
	    $result = $this->BitmaskedThing->getBits();
	    $this->assertTrue($result);
	    $this->assertTrue($result & 1);

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
	    $result = $this->BitmaskedThing->hasBit();
	    $this->assertFalse($result);

	    // test with bad id
	    $result = $this->BitmaskedThing->hasBit(999, $bit);
	    $this->assertFalse($result);

	    // test with explicit id
	    $result = $this->BitmaskedThing->hasBit($id, $bit);
	    $this->assertTrue($result);

	    // test with implicit id
	    $this->BitmaskedThing->id = $id;
	    $result = $this->BitmaskedThing->hasBit(null, $bit);
	    $this->assertTrue($result);

	    // test with string bit
	    $result = $this->BitmaskedThing->hasBit($id, 'ALL');
	    $this->assertTrue($result);

	    // test with bad string bit
	    $result = $this->BitmaskedThing->hasBit($id, 'SHOOP');
	    $this->assertFalse($result);

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

	    $result = $this->BitmaskedThing->getBits();
	    $this->assertFalse($result);

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

	    $result = $this->BitmaskedThing->getBits();
	    $this->assertTrue($result);
	    $this->assertEqual($bits, $result);

	    // test alternate data formats
	    $this->BitmaskedThing->create();
	    $this->BitmaskedThing->save(array(
		    'BitmaskedThing' => array(
			    'name' => 'ShoopShoop',
			    'desc' => 'A ShoopShoop is a type of Thing',
			    'bits' => $bits
		    )
	    ));
	    $result = $this->BitmaskedThing->getBits();
	    $this->assertTrue($result);
	    $this->assertEqual($bits, $result);

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

	    $result = $this->BitmaskedThing->getBits();
	    $this->assertTrue($result);
	    $this->assertEqual($bits, $result);

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
	    $result = $this->BitmaskedThing->getBits();
	    $this->assertFalse($result);

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
	    $this->assertTrue($result);
	    $this->assertEqual($result, $newBits);
	    $this->assertNotEqual($result, $oldBits);

    }

	/**
     * Test beforeDelete
     *
     * @return void
     */
    public function testBeforeDelete() {

	    // test enabled
	    $id = 1;
	    $alias = $this->BitmaskedThing->getBitmaskedBitAlias();

	    $this->BitmaskedThing->delete($id);

		$result = $this->BitmaskedThing->{$alias}->find('count', array(
			'conditions' => array(
				"{$alias}.model" => 'BitmaskedThing',
				"{$alias}.foreign_id" => $id
			)
		));
	    $this->assertFalse($result);

	    // test disabled
	    $this->BitmaskedThing->disableBitmasked();

	    $id = 2;

	    $this->BitmaskedThing->delete($id);

		$result = $this->BitmaskedThing->{$alias}->find('count', array(
			'conditions' => array(
				"{$alias}.model" => 'BitmaskedThing',
				"{$alias}.foreign_id" => $id
			)
		));
	    $this->assertTrue($result);

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

	    $this->assertFalse(Set::extract("/{$alias}/.", $result));
	    $this->assertEqual(count($result), 4);

	    // test enabled
	    $this->BitmaskedThing->disableBitmasked(false);

	    // test with top-level query option
	    $result1 = $this->BitmaskedThing->find('all', array(
		    'bitmask' => 1
	    ));

	    $this->assertTrue(Set::matches("/{$alias}[bits=1]", $result1));
	    $this->assertEqual(count($result1), 1);

	    // test with condition-level query option
	    $result2 = $this->BitmaskedThing->find('all', array(
		   'conditions' => array(
			    'bitmask' => 1
		   )
	    ));

	    $this->assertEqual($result2, $result1);

	    if(floatval(PHP_VERSION) >= 5.3) {

		    // test closure bitmask callback
		    $result = $this->BitmaskedThing->find('all', array(
				'bitmask' => function() {
					return 12;
				}
			));

			$this->assertTrue(Set::matches("/{$alias}[bits=12]", $result));
			$this->assertEqual(count($result), 2);

	    }

	    // test model method bitmask callback
	    $result = $this->BitmaskedThing->find('all', array(
			'bitmask' => 'getMask'
		));

		$this->assertTrue(Set::matches("/{$alias}[bits=2]", $result));
		$this->assertEqual(count($result), 1);

	    // test global function bitmask callback
	    $result = $this->BitmaskedThing->find('all', array(
			'bitmask' => 'getCustomMask'
		));

		$this->assertTrue(Set::matches("/{$alias}[bits=4]", $result));
		$this->assertEqual(count($result), 2);

	    // test flag (string) bitmask
	    $result = $this->BitmaskedThing->find('all', array(
			'bitmask' => '18PLUS'
		));

		$this->assertTrue(Set::matches("/{$alias}[bits=4]", $result));
		$this->assertEqual(count($result), 2);

	    // test flags (array) bitmask
	    $result = $this->BitmaskedThing->find('all', array(
			'bitmask' => array(
				'ALL',
				'21PLUS'
			)
		));

		$this->assertTrue(Set::matches("/{$alias}[bits=12]", $result));
		$this->assertEqual(count($result), 2);

    }

}

?>