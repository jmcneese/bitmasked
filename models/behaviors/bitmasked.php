<?php

/**
 * @package		bitmasked
 * @subpackage	bitmasked.models.behaviors
 * @author		Joshua McNeese <jmcneese@gmail.com>
 * @license		Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * @copyright	Copyright (c) 2009-2011 Joshua M. McNeese, Curtis J. Beeson
 */

/**
 * BitmaskedBehavior
 *
 * An implementation of bitwise masks for row-level operations.
 *
 * @uses ModelBehavior
 */
class BitmaskedBehavior extends ModelBehavior {

	/**
	 * settings defaults
	 *
	 * @var array
	 */
	protected $_defaults = array(
		'bits' => array(
			'ALL' => 1
		),
		'disabled' => false,
		'mask' => null,
		'default' => 1
	);

	/**
	 * bind the Bitmask model to the model in question
	 *
	 * @param	Model	$Model
	 * @return	boolean
	 */
	protected function _bind(&$Model, $conditions = array()) {
		$this->_unbind($Model);
		$alias = $this->getBitmaskedBitAlias($Model);
		return $Model->bindModel(array(
			'hasOne' => array(
				$alias => array(
					'className' => 'Bitmasked.BitmaskedBit',
					'foreignKey' => 'foreign_id',
					'dependent' => true,
					'type' => 'INNER',
					'conditions' => array_merge($conditions, array(
						"{$alias}.model" => $Model->name
					))
				)
			)
		));
	}

	/**
	 * Convenience method for getting the bits integer for a list of flags
	 *
	 * @param   Model	$Model
	 * @param   mixed	$flags
	 * @return  integer
	 */
	protected function _flagsToBits(&$Model, $flags = array()) {
		$bits = 0;
		if (!empty($flags)) {
			foreach ($flags as $flag) {
				$bit = $this->_flagToBit($Model, $flag);
				if ($bit) {
					$bits |= $bit;
				}
			}
		}
		return $bits;
	}

	/**
	 * Convenience method for getting the bit integer for an flag
	 *
	 * @param	Model	$Model
	 * @param	mixed	$flag
	 * @return	integer
	 */
	protected function _flagToBit(&$Model, $flag = null) {
		$bits = $this->settings[$Model->alias]['bits'];
		$flag = strtoupper($flag);
		return empty($flag) || !array_key_exists($flag, $bits) ? false : $bits[$flag];
	}

	/**
	 * unbind the Bitmask model from the model in question
	 *
	 * @param  Model	$Model
	 * @return boolean
	 */
	protected function _unbind(&$Model) {
		return $Model->unbindModel(array(
			'hasOne' => array(
				$this->getBitmaskedBitAlias($Model)
			)
		));
	}

	/**
	 * afterSave model callback
	 *
	 * save related bitmask row
	 *
	 * @author	Joshua McNeese
	 * @author	Ryan Morris
	 * @param	Model	$Model
	 * @param	boolean	$created
	 * @return	boolean
	 */
	public function afterSave(&$Model, $created) {
		$alias = $this->getBitmaskedBitAlias($Model);
		$requested = array();
		$data = array(
			'model' => $Model->name,
			'foreign_id' => $Model->id,
			'bits' => $this->settings[$Model->alias]['default']
		);
		if (!empty($Model->data[$Model->alias]['bits'])) {
			$requested = $Model->data[$Model->alias]['bits'];
			unset($Model->data[$Model->alias]['bits']);
		} elseif (!empty($Model->data['BitmaskedBit'])) {
			$requested = $Model->data['BitmaskedBit'];
			unset($Model->data['BitmaskedBit']);
		} elseif (!empty($Model->data[$alias])) {
			$requested = $Model->data[$alias];
			unset($Model->data[$alias]);
		}

		// don't bother with saving bits for existing records that don't provide them
		if (!$created &&
			(
				empty($requested) ||
				(is_array($requested) && empty($requested['bits']))
			)
		) {
			return true;
		}
		
		if ($this->settings[$Model->alias]['disabled']) {
			return true;
		}
		$this->_bind($Model);
		if (is_array($requested)) {
			$data = array_merge($data, $requested);
		} elseif (is_numeric($requested)) {
			$data['bits'] = $requested;
		}
		if (isset($data['id'])) {
			unset($data['id']);
		}
		if ($created) {
			$Model->{$alias}->create();
		} else {
			// go get existing bits for this row
			$previous = $this->getBitmaskedBit($Model);
			if (!empty($previous)) {
				$Model->{$alias}->id = $previous[$alias]['id'];
			}
		}
		if (!$Model->{$alias}->save($data)) {
			return false;
		}
		$Model->data[$alias] = array(
			'id' => $Model->{$alias}->id
		) + $data;
		return true;
	}

	/**
	 * afterDelete model callback
	 *
	 * cleanup any related bitmask rows
	 *
	 * @param	Model	$Model
	 * @param	boolean	$cascade
	 * @return	mixed
	 */
	public function beforeDelete(&$Model, $cascade = true) {
		if (!$this->settings[$Model->alias]['disabled'] && $cascade && !$Model->Behaviors->attached('SoftDeletable')) {
			$this->_bind($Model);
		}
		return parent::beforeDelete($Model, $cascade);
	}

	/**
	 * beforeFind model callback
	 *
	 * if we are checking bitmasks, then the appropriate modifications are
	 * made to the original query to filter out denied rows
	 *
	 * @param	Model	$Model
	 * @param	array	$queryData
	 * @return	mixed
	 */
	public function beforeFind(&$Model, $queryData) {
		$bitmask = null;
		if (isset($queryData['bitmask'])) {
			$bitmask = $queryData['bitmask'];
			unset($queryData['bitmask']);
		}
		if (isset($queryData['conditions']) && is_array($queryData['conditions']) && isset($queryData['conditions']['bitmask'])) {
			$bitmask = $queryData['conditions']['bitmask'];
			unset($queryData['conditions']['bitmask']);
		}
		if ($this->settings[$Model->alias]['disabled'] || $bitmask === false) {
			$this->_unbind($Model);
			return $queryData;
		}
		$alias = $this->getBitmaskedBitAlias($Model);
		$bitmask = empty($bitmask) // nothing was specified in $queryData
			? $this->settings[$Model->alias]['mask'] // use config
			: $bitmask;
		if (!is_numeric($bitmask)) {
			switch (true) {
				case is_array($bitmask):
					$bitmask = $this->_flagsToBits($Model, $bitmask);
					break;
				case is_callable($bitmask):
					$bitmask = call_user_func($bitmask);
					break;
				case is_string($bitmask) && method_exists($Model, $bitmask):
					$bitmask = call_user_func(array(&$Model, $bitmask));
					break;
				default:
					$bitmask = $this->_flagToBit($Model, $bitmask);
			}
		}
		if(!empty($bitmask)) {
			/**
			 * bind the BitmaskedBit model as an INNER JOIN to the existing query, filtering out records without the
			 * requisite bits
			 */
			$this->_bind($Model, array(
				"{$alias}.bits & {$bitmask} <> 0"
			));
		}
		return $queryData;
	}

	/**
	 * turn arrays of flags into bits, if necessary
	 *
	 * @param  Model	$Model
	 * @return mixed
	 */
	public function beforeSave(&$Model) {
		$alias = $this->getBitmaskedBitAlias($Model);
		if (!empty($Model->data[$Model->alias]['bits']) && is_array($Model->data[$Model->alias]['bits'])) {
			$Model->data[$Model->alias]['bits'] = $this->_flagsToBits($Model, $Model->data[$Model->alias]['bits']);
		} elseif (!empty($Model->data['BitmaskedBit']['bits']) && is_array($Model->data['BitmaskedBit']['bits'])) {
			$Model->data['BitmaskedBit']['bits'] = $this->_flagsToBits($Model, $Model->data['BitmaskedBit']['bits']);
		} elseif (!empty($Model->data[$alias]['bits']) && is_array($Model->data[$alias]['bits'])) {
			$Model->data[$alias]['bits'] = $this->_flagsToBits($Model, $Model->data[$alias]['bits']);
		}
		return parent::beforeSave($Model);
	}

	/**
	 * delete the bitmask for the record
	 *
	 * @param	Model	$Model
	 * @param	mixed	$id
	 * @return	mixed
	 */
	public function deleteBitmaskedBit(&$Model, $id = null) {
		$id = empty($id) ? $Model->id : $id;
		if (empty($id)) {
			return false;
		}
		$this->_bind($Model);
		$alias = $this->getBitmaskedBitAlias($Model);
		return $Model->{$alias}->deleteAll(array(
			"{$alias}.model" => $Model->name,
			"{$alias}.foreign_id" => $id
		));
	}

	/**
	 * disable Bitmasked for the model
	 *
	 * @param	Model	$Model
	 * @param	boolean	$disable
	 * @return	null
	 */
	public function disableBitmasked(&$Model, $disable = true) {
		$this->settings[$Model->alias]['disabled'] = $disable;
	}

	/**
	 * get the bits for a record
	 *
	 * @param	Model	$Model
	 * @param	integer	$id
	 * @return	mixed
	 */
	public function getBits(&$Model, $id = null) {
		$id = empty($id) ? $Model->id : $id;
		if (empty($id)) {
			return false;
		}
		$alias = $this->getBitmaskedBitAlias($Model);
		$bitmask = $this->getBitmaskedBit($Model, $id);
		if (!$bitmask) {
			return false;
		}
		return empty($bitmask[$alias]['bits']) ? 1 : (int)$bitmask[$alias]['bits'];
	}

	/**
	 * get the bitmask for the record
	 *
	 * @param	Model	$Model
	 * @param	mixed	$id
	 * @return	mixed
	 */
	public function getBitmaskedBit(&$Model, $id = null) {
		$id = empty($id) ? $Model->id : $id;
		if (empty($id)) {
			return false;
		}
		$this->_bind($Model);
		$alias = $this->getBitmaskedBitAlias($Model);
		return $Model->{$alias}->find('first', array(
			'conditions' => array(
				"{$alias}.model" => $Model->name,
				"{$alias}.foreign_id" => $id
			)
		));
	}

	/**
	 * get alias for the BitmaskedBit model
	 *
	 * @param	Model	$Model
	 * @return	mixed
	 */
	public function getBitmaskedBitAlias(&$Model) {
		return "{$Model->alias}BitmaskedBit";
	}

	/**
	 * check if a record has a particular bit
	 *
	 * @param	Model	$Model
	 * @param	integer	$id
	 * @param	integer	$bit
	 * @return	boolean
	 */
	public function hasBit(&$Model, $id = null, $bit = null) {
		$id = empty($id) ? $Model->id : $id;
		if (empty($id) || empty($bit)) {
			return false;
		}
		$bits = $this->getBits($Model, $id);
		if (!$bits) {
			return false;
		}
		if (is_string($bit)) {
			$bit = $this->_flagToBit($Model, $bit);
			if (!$bit) {
				return false;
			}
		}
		return ((int)$bits & (int)$bit) <> 0;
	}

	/**
	 * getter to determine if Bitmasked is enabled
	 *
	 * @param	Model	$Model
	 * @return	boolean
	 */
	public function isBitmaskedDisabled(&$Model) {
		return $this->settings[$Model->alias]['disabled'];
	}

	/**
	 * Behavior configuration
	 *
	 * @param	Model	$Model
	 * @param	array	$config
	 * @return	void
	 */
	public function setup(&$Model, $config = array( )) {
		$config = (is_array($config) && !empty($config)) ? array_merge($this->_defaults, $config) : $this->_defaults;
		if (empty($config['bits'])) {
			$this->disableBitmasked($Model);
			return false;
		}
		if (Set::numeric(array_keys($config['bits']))) {
			$last = 1;
			$bits = array('ALL' => 1);
			foreach ($config['bits'] as $flag) {
				$bits[$flag] = $last = $last * 2;
			}
			$config['bits'] = $bits;
		}
		$this->settings[$Model->alias] = $config;
	}

}

?>