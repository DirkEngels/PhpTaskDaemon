<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Executor\Status
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Executor\Status;

/**
 * 
 * The abstract class encapsulate a set of methods of the SharedMemory class. 
 *
 */
abstract class AbstractClass {
	/**
	 * @var \PhpTaskDaemon\SharedMemory
	 */
	protected $_sharedMemory;

	/**
	 * 
	 * The constructor sets the shared memory object. A default shared memory
	 * object instance will be created when none provided.
	 * @param \PhpTaskDaemon\SharedMemory $sharedMemory
	 */
	public function __construct(\PhpTaskDaemon\Daemon\Ipc\SharedMemory $sharedMemory = null) {
		$this->setSharedMemory($sharedMemory);
	}
	
	/**
	 * 
	 * Unset the shared memory at destruction time.
	 */
	public function __destruct() {
		$this->_sharedMemory->remove();
		unset($this->_sharedMemory); 
	}
		
	/**
	 *
	 * Returns the shared memory object
	 * @return PhpTaskDaemon\SharedMemory
	 */
	public function getSharedMemory() {
		return $this->_sharedMemory;
	}

	/**
	 *
	 * Sets a shared memory object
	 * @param \PhpTaskDaemon\Daemon\Ipc\SharedMemory $sharedMemory
	 * @return $this
	 */
	public function setSharedMemory($sharedMemory) {
		if (!is_a($sharedMemory, '\PhpTaskDaemon\Daemon\Ipc\SharedMemory')) {
			$sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory('status-' . getmypid());
		}
		$this->_sharedMemory = $sharedMemory;
		return true;
	}

	/**
	 * 
	 * Get one or more status variables. When a key is provided and exists, the
	 * corresponding value will be returning. If no key is given, all
	 * registered keys and values will be returned. 
	 * @param string|null $key
	 */
	public function get($key = null) {
		if ($key != null) {
			return $this->_sharedMemory->getVar($key);
		}
		return $this->_sharedMemory->getKeys();
	}
	
	/**
	 * 
	 * Store the status of variable of using a shared memory segment. 
	 * @param integer $percentage
	 * @param string $message
	 * @return bool
	 */
    public function set($percentage, $message = null) {
    	$this->_sharedMemory->setVar('percentage', $percentage);
    	if ($message != null) {
    		$this->_sharedMemory->setVar('message', $message);
    	}
    	return true;
    }
}