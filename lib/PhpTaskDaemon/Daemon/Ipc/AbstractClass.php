<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Ipc
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon\Ipc;

/**
 * The abstract class implements storing the variable keys and provides a
 * method for retrieving all registered keys.
 * 
 */
abstract class AbstractClass {
	
	/**
	 * This array contains the keys of all registered variables.
	 * @var array
	 */
	protected $_keys = array();

	/**
	 * 
	 * Returns the registered keys.
	 * @return array
	 */
	public function getKeys() {
		return $this->_keys;
	}

	/**
	 * Returns all the registered keys with corresponding values.
	 * @return array
	 */
	public function get() {
		$keys = $this->getKeys();
		$data = array();
		foreach($keys as $nr => $key) {
			$value = $this->getVar($nr);
//			echo "ADDING " . $nr . " => " . $value . "\n";
			$data[$nr] = $value;
		}
		return $data;
	}
}	
