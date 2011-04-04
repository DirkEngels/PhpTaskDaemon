<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Executor\Status
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Task\Executor\Status;

abstract class AbstractClass {
	/**
	 * @var \PhpTaskDaemon\SharedMemory
	 */
	protected $_sharedMemory;
	
	public function __construct(\PhpTaskDaemon\SharedMemory $sharedMemory = null) {
		$this->setSharedMemory($sharedMemory);
	}
	
	public function __destruct() {
		$this->_sharedMemory->remove();
		unset($this->_sharedMemory); 
	}
		
	public function getSharedMemory() {
		return $this->_sharedMemory;
	}
	public function setSharedMemory($sharedMemory) {
		if (!is_a($sharedMemory, '\PhpTaskDaemon\Daemon\Ipc\SharedMemory')) {
			$sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory('test');
		}
		$this->_sharedMemory = $sharedMemory;
	}

	public function get($key = null) {
		if ($key != null) {
			return $this->_sharedMemory->getVar($key);
		}
		return $this->_sharedMemory->getKeys();
	}
    public function set($percentage, $message = null) {
    	if (is_a($this->_sharedMemory, '\PhpTaskDaemon\Daemon\Ipc\SharedMemory')) {
	    	$this->_sharedMemory->setVar('percentage', $percentage);
	    	if ($message != null) {
	    		$this->_sharedMemory->setVar('message', $message);
	    	}
    	}
    }
}