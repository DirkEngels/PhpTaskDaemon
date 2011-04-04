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
	
	const STATUS_QUEUED = 'Queued';
	const STATUS_RUNNING = 'Running';
	const STATUS_DONE = 'Done';
	const STATUS_FAILED = 'Failed';

	public function __construct(\PhpTaskDaemon\SharedMemory $sharedMemory = null) {
		$this->setSharedMemory($sharedMemory);
		$this->_initializeStatus(self::STATUS_QUEUED);
		$this->_initializeStatus(self::STATUS_RUNNING);
		$this->_initializeStatus(self::STATUS_DONE);
		$this->_initializeStatus(self::STATUS_FAILED);
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
		}
		$this->_sharedMemory = $sharedMemory;
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
	 * Increments the statistics for a certain status
	 * 
	 * @param string $status
	 * @return integer
	 */
    public function increment($status = self::STATUS_DONE) {
    	$this->_initializeStatus($status);
    	// Update shared memory key +1
    	$this->_sharedMemory->setVar(
    		$status,
    		$this->_sharedMemory->getVar($status)
    	);
    }
    
    /**
     * Initializes the statistics array for a certain status.
     * 
     * @param string $status
     * @return bool
     */
    private function _initializeStatus($status) {
    	$keys = $this->_sharedMemory->get();
    	if (!in_array($status, $keys)) {
    		$this->_sharedMemory->setVar($status, 0);
    		return true;
    	}
    	return false;
	}
    
}