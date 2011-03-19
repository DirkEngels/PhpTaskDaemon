<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Executor\Status
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Executor\Status;

abstract class AbstractClass {
	/**
	 * @var \PhpTaskDaemon\SharedMemory
	 */
	protected $_shm;


	public function getShm() {
		return $this->_shm;
	}
	public function setShm($shm) {
		$this->_shm = $shm;
	}

	public function get($key = null) {
		if ($key != null) {
			return $this->_shm->getVar($key);
		}
		return $this->_shm->getKeys();
	}
    public function set($percentage, $message = null) {
    	$this->_shm->setVar('percentage', $percentage);
    	if ($message != null) {
    		$this->_shm->setVar('message', $message);
    	}
    }
}