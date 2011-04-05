<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Executor
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Task\Executor;

/**
 * 
 * The executor abstract class implements a method for updating the status and
 * provides setters and getters for the status and job instance. 
 */
abstract class AbstractClass {
	protected $_job = null;
	protected $_status = null;

	/**
	 * 
	 * Optional a executor status object can be provided. 
	 * @param \PhpTaskDaemon\Task\Executor\Status $status
	 */
	public function __construct($status = null) {
		$this->setStatus($status);
	}
	
	/**
	 * 
	 * Returns the current job.
	 * @return \PhpTaskDaemon\Task\Job
	 */
	public function getJob() {
		return $this->_job;
	}
	
	/**
	 * 
	 * Sets the current job
	 * @param \PhpTaskDaemon\Task\Job $job
	 */
	public function setJob($job) {
		$this->_job = $job;
	}
	
	/**
	 * 
	 * Returns the current status object, if set.
	 * @return \PhpTaskDaemon\Task\Executor\Status $status
	 */
	public function getStatus() {
		return $this->_status;
	}
	
	/**
	 * 
	 * Sets the current status object
	 * @param \PhpTaskDaemon\Task\Executor\Status $status
	 */
	public function setStatus($status) {
		$this->_status = $status;
	}

	/**
	 * 
	 * Updates the status of the current job in shared memory.
	 * @param integer $percentage
	 * @param string|null $message
	 * @return bool
	 */
	public function updateStatus($percentage, $message = null) {
		if ($this->_status != null) {
			return $this->_status->set($percentage, $message);
		}
		return false;
	}
}