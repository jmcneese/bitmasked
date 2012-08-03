<?php

/**
 * @package		bitmasked
 * @subpackage	bitmasked.test.case
 * @author		Joshua McNeese <jmcneese@gmail.com>
 * @license		Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * @copyright	Copyright (c) 2009-2012 Joshua M. McNeese, Curtis J. Beeson
 */

/**
 * BitmaskedPluginTest
 *
 * @uses CakeTestSuite
 */
class BitmaskedPluginTest extends CakeTestSuite {

    public static function suite() {
        $suite = new CakeTestSuite('All BitmaskedPlugin tests');
        $suite->addTestFile(dirname(__FILE__) . DS . 'Model' . DS . 'BitmaskedBitTest.php');
        $suite->addTestFile(dirname(__FILE__) . DS . 'Model' . DS . 'Behavior' . DS . 'BitmaskedBehaviorTest.php');
        return $suite;
    }

}