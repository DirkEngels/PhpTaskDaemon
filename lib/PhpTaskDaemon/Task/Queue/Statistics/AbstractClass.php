<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Statistics
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Task\Queue\Statistics;

abstract class AbstractClass {	
	protected $_statistics = array();
	protected $_sharedMemory;
	
	const STATUS_LOADED = 'Loaded';
	const STATUS_QUEUED = 'Queued';
	const STATUS_RUNNING = 'Running';
	const STATUS_DONE = 'Done';
	const STATUS_FAILED = 'Failed';

	public function __construct(\PhpTaskDaemon\SharedMemory $sharedMemory = null) {
		$this->setSharedMemory($sharedMemory);
	}
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
			$sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory('statistics-' . getmypid());
			$this->_sharedMemory = $sharedMemory;
			$this->_initializeStatus(self::STATUS_LOADED);
			$this->_initializeStatus(self::STATUS_QUEUED);
			$this->_initializeStatus(self::STATUS_RUNNING);
			$this->_initializeStatus(self::STATUS_DONE);
			$this->_initializeStatus(self::STATUS_FAILED);
		}
		return $this;
	}
	
	/**
	 * Returns an array with the number of executed tasks grouped per status.
	 * 
	 * @param array $status
	 * @return array
	 */
    public function get($status = null) {
    	if ($this->_sharedMemory->hasKey($status)) {
    		$this->_initializeStatus($status);
    		return $this->_statistics[$status];
    	}
    	return $this->_statistics;
    }

    /**
     * 
     * (Re)Sets a status count
     * @param string $status
     * @param integer $count
     */
    public function setStatusCount($status = self::STATUS_DONE, $count = 0) {
    	$this->_sharedMemory->setVar($status, $count);
    }
    
	/**
	 * Increments the statistics for a certain status
	 * 
	 * @param string $status
	 * @return integer
	 */
    public function incrementStatus($status = self::STATUS_DONE) {
    	$this->_initializeStatus($status);
    	// Update shared memory key +1
    	$this->_sharedMemory->incrementVar($status);
    }

    /**
     * 
     * (Re)Sets the queue count.
     * @param integer $count
     */
    public function setQueueCount($count) {
    	$this->setStatusCount(self::STATUS_QUEUED, $count);
    	$this->setStatusCount(self::STATUS_LOADED, $count);
    }
    /**
     * 
     * Decrements the queue count (after finishing a single job).
     */
    public function decrementQueue() {
    	$this->_sharedMemory->decrementVar(self::STATUS_QUEUED);
    }
    
    /**
     * Initializes the statistics array for a certain status.
     * 
     * @param string $status
     * @return bool
     */
    private function _initializeStatus($status) {
    	if (!is_a($this->_sharedMemory, '\PhpTaskDaemon\Daemon\Ipc\SharedMemory')) {
    		return false;
    	}
    	$keys = $this->_sharedMemory->get();
    	if (!in_array($status, $keys)) {
    		$this->_sharedMemory->setVar($status, 0);
    		return true;
    	}
    	return false;
	}
    
}