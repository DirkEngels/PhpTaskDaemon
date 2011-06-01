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
 * The abstract class encapsulate a set of methods of the Ipc class. 
 *
 */
abstract class AbstractClass {
	/**
	 * @var \PhpTaskDaemon\Ipc
	 */
	protected $_ipc;

	/**
	 * 
	 * The constructor sets the shared memory object. A default shared memory
	 * object instance will be created when none provided.
	 * @param \PhpTaskDaemon\Ipc $ipc
	 */
	public function __construct(\PhpTaskDaemon\Daemon\Ipc\AbstractClass $ipc = null) {
		$this->setIpc($ipc);
	}
	
	/**
	 * 
	 * Unset the shared memory at destruction time.
	 */
	public function __destruct() {
		$this->_ipc->remove();
		unset($this->_ipc); 
	}
		
	/**
	 *
	 * Returns the shared memory object
	 * @return PhpTaskDaemon\Ipc
	 */
	public function getIpc() {
		return $this->_ipc;
	}

	/**
	 *
	 * Sets a shared memory object
	 * @param \PhpTaskDaemon\Daemon\Ipc\Ipc $ipc
	 * @return $this
	 */
	public function setIpc($ipc) {
		if (!is_a($ipc, '\PhpTaskDaemon\Daemon\Ipc\AbstractClass')) {
			$ipc = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory('status-' . getmypid());
		}
		$this->_ipc = $ipc;
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
			return $this->_ipc->getVar($key);
		}
		return $this->_ipc->getKeys();
	}
	
	/**
	 * 
	 * Store the status of variable of using a shared memory segment. 
	 * @param integer $percentage
	 * @param string $message
	 * @return bool
	 */
    public function set($percentage, $message = null) {
    	$this->_ipc->setVar('percentage', $percentage);
    	if ($message != null) {
    		$this->_ipc->setVar('message', $message);
    	}
    	return true;
    }
}