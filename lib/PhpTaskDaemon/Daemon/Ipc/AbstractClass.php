<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Executor
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Daemon\Ipc;

abstract class AbstractClass {
	
	/**
	 * This array contains the keys of all registered variables.
	 * @var array
	 */
	protected $_keys = array();

	/**
	 * 
	 * Returns the registered keys
	 * @return array
	 */
	public function getKeys() {
		return $this->_keys;
	}

	/**
	 * 
	 * Sets the registered keys
	 * @param array $keys
	 * @return $this
	 */
	public function setKeys($keys) {
		if (is_array($keys)) {
			$this->_keys = $keys;
		}
		return $this;
	}
}	
