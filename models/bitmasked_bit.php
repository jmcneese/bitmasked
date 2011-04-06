<?php

/**
 * @package     bitmasked
 * @subpackage  bitmasked.tests.cases.behaviors
 * @author      Joshua McNeese <jmcneese@gmail.com>
 * @license		Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * @copyright	Copyright (c) 2009-2011 Joshua M. McNeese, Curtis J. Beeson
 */

/**
 * BitmaskedBit Model
 *
 * Model for manipulating bitmask data
 *
 * @uses AppModel
 */
class BitmaskedBit extends AppModel {

	/**
	 * @var array
	 */
	public $validate = array(
		'model' => array(
			'rule' => array('notEmpty'),
			'message' => 'Model cannot be empty',
			'required' => true,
			'on' => 'create'
		),
		'foreign_id' => array(
			'rule' => array('notEmpty'),
			'message' => 'Model cannot be empty',
			'required' => true,
			'on' => 'create'
		),
		'bits' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				'message' => 'Bits cannot be empty',
				'required' => true,
				'on' => 'create'
			),
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Bits must be numeric'
			)
		)
	);

}

?>