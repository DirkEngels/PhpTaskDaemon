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
	 * Returns all the registered keys and values
	 * @return array
	 */
	public function get() {
		$keys = $this->getKeys();
		$data = array();
		foreach($keys as $nr => $key) {
			$data[$nr] = $this->getVar($nr);
		}
		return $data;
	}
}	
