<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager;

/**
 * 
 * This is the abstract class of a Daemon_Manager. It provides the basic 
 * methods needed for almost all managers. 
 */
abstract class AbstractClass {
	/**
	 * 
	 * The log file object
	 * @var Zend_Log
	 */
	protected $_log = null;

	/**
	 * 
	 * Pid manager object. This class is repsonsible for storing the current, 
	 * parent and child process IDs.
	 * @var \PhpTaskDaemon\Daemon\Pid\Manager
	 */
	protected $_pidManager = null;
	
	/**
	 * Queue object
	 * @var \PhpTaskDaemon\Task\Queue\AbstractClass
	 */
	protected $_queue = null;
	
	/**
	 * Executor object
	 * @var \PhpTaskDaemon\Task\Executor\AbstractClass
	 */
	protected $_executor = null;

	/**
	 * Time to wait in milliseconds before running the next task.
	 * 
	 * @var integer
	 */
	protected $_sleepTimeExecutor = 10;

	/**
	 * Time to wait in milliseconds before loading the queue again.
	 * 
	 * @var integer
	 */
	protected $_sleepTimeQueue = 3000000;
	
	/**
	 * 
	 * A manager requires a executor and queue object. In case of a gearman
	 * worker the queue object is optional. 
	 * @param \PhpTaskDaemon\Task\Executor\AbstractClass $executor
	 * @param \PhpTaskDaemon\Task\Queue\AbstractClass $queue
	 */
	public function __construct($executor, $queue = null) {
		$this->setQueue($queue);
		$this->setExecutor($executor);
	}

	/**
	 * 
	 * Destroy the shared memory object
	 */	
	public function __destruct() {
	}

	/**
	 * 
	 * Initializes the pid manager
	 * @param int $parentPid
	 */
	public function init($parentPid = null) {
		$this->_pidManager = new \PhpTaskDaemon\Daemon\Pid\Manager(
			getmypid(), 
			$parentPid
		);
	}

	/**
	 * 
	 * Returns the log object
	 * @return Zend_Log
	 */
	public function getLog() {
		return $this->_log;
	}

	/**
	 * 
	 * Sets the log object
	 * @param Zend_Log $log
	 * @return $this
	 */
	public function setLog(\Zend_Log $log) {
		$this->_log = $log;
		return $this;
	}

	/**
	 * 
	 * Returns the pid manager of the task manager
	 * @return \PhpTaskDaemon\Pid\Manager
	 */
	public function getPidManager() {
		return $this->_pidManager;
	}
	
	/**
	 * 
	 * Sets the pid manager of the task manager
	 * @param \PhpTaskDaemon\Pid\Manager $pidManager
	 * @return $this
	 */
	public function setPidManager(\PhpTaskDaemon\Daemon\Pid\Manager $pidManager) {
		$this->_pidManager = $pidManager;
		return $this;
	}

	/**
	 * 
	 * Returns the current loaded queue array
	 * @return \PhpTaskDaemon\Task\Manager\Trigger\AbstractClass
	 */
	public function getTrigger() {
		return $this->_trigger;
	}

	/**
	 * 
	 * Sets the current queue to process.
	 * @param \PhpTaskDaemon\Task\Manager\Trigger\AbstractClass $trigger
	 * @return $this
	 */
	public function setTrigger($trigger) {
		if (!is_a($trigger, '\PhpTaskDaemon\Task\Manager\Trigger\AbstractClass')) {
			$trigger = new \PhpTaskDaemon\Task\Manager\Trigger\Interval();
		}
		$this->_trigger = $trigger;

		return $this;
	}

	/**
	 * 
	 * Returns the process object
	 * @return \PhpTaskDaemon\Task\Manager\AbstractClass
	 */
	public function getProcess() {
		return $this->_process;
	}

	/**
	 * 
	 * Sets the current executor object.
	 * @param \PhpTaskDaemon\Executor\AbstractClass $executor
	 * @return $this
	 */
	public function setProcess($process) {
		if (!is_a($process, '\PhpTaskDaemon\Task\Manager\Process\AbstractClass')) {
			$process = new \PhpTaskDaemon\Task\Manager\Process\BaseClass();
		}
		$this->_process = $process;

		return $this;
	}

	/**
	 * 
	 * Logs a message to the log object, if set.
	 * @param string $message
	 * @param integer $level
	 */
	public function log($message, $level = \Zend_Log::INFO) {
		if (is_a($this->_log, 'Zend_Log')) {
			return $this->_log->log($message, $level);
		}
		return false;
	}

	/**
	 * 
	 * Starts the manager
	 */
	public function runManager() {
		// Override signal handler
		$this->_sigHandler = new \PhpTaskDaemon\Daemon\Interrupt\Signal(
			get_class($this),
			$this->_log, 
			array(&$this, 'sigHandler')
		);

		if ($this->getLog() === null) {
			$this->setLog($this->_log);
		}
		if ($this->getPidManager() === null) {
			$this->setPidManager($this->_pidManager);
		}
		$this->execute();
	}

	/**
	 * 
	 * POSIX Signal handler callback
	 * @param $sig
	 */
	public function sigHandler($sig) {
		switch ($sig) {
			case SIGTERM:
				// Shutdown
				$this->_log->log('Application (TASK) received SIGTERM signal (shutting down)', \Zend_Log::DEBUG);
				break;
			case SIGCHLD:
				// Halt
				$this->_log->log('Application (TASK) received SIGCHLD signal (halting)', \Zend_Log::DEBUG);		
				while (pcntl_waitpid(-1, $status, WNOHANG) > 0);
				break;
			case SIGINT:
				// Shutdown
				$this->_log->log('Application (TASK) received SIGINT signal (shutting down)', \Zend_Log::DEBUG);
				break;
			default:
				$this->_log->log('Application (TASK) received ' . $sig . ' signal (unknown action)', \Zend_Log::DEBUG);
				break;
		}
		exit;
	}

	/**
	 * 
	 * Process a single task: set job input, reset status, run and update
	 * statistics
	 * @param \PhpTaskDaemon\Task\Job\AbstractClass $job
	 */
	protected function _processTask(\PhpTaskDaemon\Task\Job\AbstractClass $job) {
		// Set manager input
 		$this->log("Started: " . $job->getJobId(), \Zend_Log::DEBUG);
		$this->getExecutor()->setJob($job);
		
		// Update Status before and after running the task
		$this->getExecutor()->updateStatus(0);
		$job = $this->getExecutor()->run();
		$this->getExecutor()->updateStatus(100);
		
		// Log and sleep for a while
		usleep($this->_sleepTimeExecutor);
		$this->log($job->getOutputVar('returnStatus') . ": " . $job->getJobId(), \Zend_Log::DEBUG);			
		$this->getQueue()->updateStatistics($job->getOutputVar('returnStatus'));

		// Reset status and decrement queue
		$this->getExecutor()->updateStatus(0);
		$this->getQueue()->updateQueue();

		return $job->getOutputVar('returnStatus');
	}

	/**
	 * 
	 * The sleep function for an interval manager
	 */
	protected function _sleep() {
		// Sleep
		$this->log("Sleeping for : " . $this->_sleepTimeQueue . " micro seconds", \Zend_Log::DEBUG);
		usleep($this->_sleepTimeQueue);
	}
}