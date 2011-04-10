<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Queue\Statistics
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Queue\Statistics;

/**
 * 
 * The abstract queue statistics class implements methods for setting/change
 * the status count of executed jobs and the number of loaded and processed
 * items in the queue.
 */
abstract class AbstractClass {	
	protected $_sharedMemory;
	
	const STATUS_LOADED = 'loaded';
	const STATUS_QUEUED = 'queued';
	const STATUS_RUNNING = 'running';
	const STATUS_DONE = 'done';
	const STATUS_FAILED = 'failed';

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
		if (is_a($this->_sharedMemory, '\PhpTaskDaemon\Daemon\Ipc\SharedMemory')) {
			unset($this->_sharedMemory);
		} 
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
			$sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory('statistics-' . getmypid());
		}
		$this->_sharedMemory = $sharedMemory;
		$this->_initializeStatus(self::STATUS_LOADED);
		$this->_initializeStatus(self::STATUS_QUEUED);
		$this->_initializeStatus(self::STATUS_RUNNING);
		$this->_initializeStatus(self::STATUS_DONE);
		$this->_initializeStatus(self::STATUS_FAILED);
		return true;
	}
	
	/**
	 * Returns an array with the number of executed tasks grouped per status.
	 * 
	 * @param string $status
	 * @return array
	 */
    public function get($status = null) {
    	if (is_null($status)) {
    		return $this->_sharedMemory->get();
    	}
    	if (!in_array($status, $this->_sharedMemory->getKeys())) {
    		$this->_initializeStatus($status);
    	}
    	return $this->_sharedMemory->getVar($status);
    }

    /**
     * 
     * (Re)Sets a status count
     * @param string $status
     * @param integer $count
     * @return bool
     */
    public function setStatusCount($status = self::STATUS_DONE, $count = 0) {
    	if (!in_array($status, $this->_sharedMemory->getKeys())) {
    		$this->_initializeStatus($status);
    	}
    	return $this->_sharedMemory->setVar($status, $count);
    }
    
	/**
	 * Increments the statistics for a certain status
	 * 
	 * @param string $status
	 * @return integer
	 */
    public function incrementStatus($status = self::STATUS_DONE) {
//    	$this->_initializeStatus($status);
    	// Update shared memory key +1
    	return $this->_sharedMemory->incrementVar($status);
    }

    /**
     * 
     * (Re)Sets the queue count.
     * @param integer $count
     */
    public function setQueueCount($count = 0) {
    	$this->setStatusCount(self::STATUS_QUEUED, $count);
    	$this->setStatusCount(self::STATUS_LOADED, $count);
    	return $count;
    }
    /**
     * 
     * Decrements the queue count (after finishing a single job).
     */
    public function decrementQueue() {
    	return $this->_sharedMemory->decrementVar(self::STATUS_QUEUED);
    }
    
    /**
     * Initializes the statistics array for a certain status.
     * 
     * @param string $status
     * @return bool
     */
    private function _initializeStatus($status) {
    	$keys = $this->_sharedMemory->get();
    	echo var_dump($keys);
    	if (!in_array($status, $keys)) {
    		$this->_sharedMemory->setVar($status, 0);
    		return true;
    	}
    	return false;
    
	}
}