<?php
/**
 * @package SiteSpeed
 * @subpackage Daemon\Task
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace SiteSpeed\Daemon\Task;

/**
 *
 * Tasks are an important function of the application. It provides an api for
 * defining task by extending this abstract class and implementing two methods
 * as defined in the interface. The task can then be executed by a manager,
 * which determines when and how to run the task.
 *
 */
class AbstractClass {

	/**
	 *
	 * This variable needs to contain one of the defined manager types.
	 * @var string
	 */
	static protected $_managerType = \SiteSpeed\Daemon\Manager\AbstractClass::PROCESS_TYPE_INTERVAL;


	/**
	 *
	 * Pid manager object. This class is repsonsible for storing the current,
	 * parent and child process IDs.
	 * @var SiteSpeed\Daemon\Pid\Manager
	 */
	protected $_pidManager = null;

	/**
	 * The execution time it to process a single task.
	 * @var int
	 */
	protected $_executionTime = 0;

	/**
	 *
	 * The log file object
	 * @var Zend_Log
	 */
	protected $_log = null;

	/**
	 *
	 * The shared memory object
	 * @var Dew_Daemon_SharedMemory
	 */
	protected $_shm = null;

	/**
	 * This name of the task, which will be extracted from the class name.
	 * @var string
	 */
	protected $_name = null;

	/**
	 *
	 * This variable is an array containing the input data in order to execute
	 * the task.
	 * @var array
	 */
	protected $_input = array();

	/**
	 *
	 * Constructor with optional arguments to provide a logger, pidManager
	 * and/or shared memory object.
	 * @param \Zend_Log $log
	 * @param \SiteSpeed\Daemon\Pid\Manager $pidManager
	 * @param \SiteSpeed\Daemon\SharedMemory $shm
	 */
	public function __construct($log = null, $pidManager = null, $shm = null) {
		if (is_a($log, '\Zend_Log')) {
			$this->setLog($log);
		}
		if (is_a($pidManager, '\SiteSpeed\Daemon\Pid\Manager')) {
			$this->setPidManager($pidManager);
		}
		if (is_a($shm, '\SiteSpeed\Daemon\SharedMemory')) {
			$this->_shm = $shm;
		}

	}

	/**
	 *
	 * Clean up objects to free memory.
	 */
	public function __destruct() {
	}

	/**
	 *
	 * Returns the name of the task/manager instance
	 * @return string
	 */
	public function getName() {
		if ($this->_name === null) {
			$this->_name = preg_replace('/^Dew_Daemon_Task_/', '', get_class($this));
		}
		return $this->_name;
	}

	/**
	 *
	 * Sets the name of task/manager instance
	 * @param string $name
	 * @return $this
	 */
	public function setName($name) {
		$this->_name = $name;
		return $this;
	}

	/**
	 *
	 * Returns the manager type
	 * @return string
	 */
	static public function getManagerType() {
		return self::$_managerType;
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
	 * Returns the shared memory object
	 * @return SiteSpeed\Daemon\SharedMemory
	 */
	public function getShm() {
		return $this->_shm;
	}

	/**
	 *
	 * Sets a shared memory object
	 * @param \SiteSpeed\Daemon\SharedMemory $shm
	 */
	public function setShm(\SiteSpeed\Daemon\SharedMemory $shm) {
		$this->_shm = $shm;
		$this->_shm->setVar('type', $this::$_managerType);
	}

	/**
	 *
	 * Returns the pid manager of the task manager
	 * @return \SiteSpeed\Daemon\Pid\Manager
	 */
	public function getPidManager() {
		return $this->_pidManager;
	}

	/**
	 *
	 * Sets the pid manager of the task manager
	 * @param \SiteSpeed\Daemon\Pid\Manager $pidManager
	 * @return $this
	 */
	public function setPidManager(\SiteSpeed\Daemon\Pid\Manager $pidManager) {
		$this->_pidManager = $pidManager;
		return $this;
	}

	/**
	 *
	 * Returns the task input data
	 * @return array
	 */
	public function getTaskInput() {
		return $this->_input;
	}

	/**
	 *
	 * Sets the task input data
	 * @param array $input
	 * @return $this
	 */
	public function setTaskInput($input) {
		if (is_array($input)) {
			$this->_input = $input;
		}
		return $this;
	}

	/**
	 *
	 * Updates the shared memory about the queue
	 * @param int $count
	 */
	public function updateMemoryQueue($count, $loaded = null) {
		if ($loaded !== null) {
			$this->_shm->setVar('loaded', $count);
		}
		$this->_shm->setVar('count', $count);
	}

	/**
	 *
	 * Updates the shared memory about the current task progress
	 * @param int $progress
	 * @param string $message
	 */
	public function updateMemoryTask($progress, $message = '') {
		$this->_shm->setVar('task-' . getmypid() . '-memory', memory_get_usage());
		$this->_shm->setVar('task-' . getmypid() . '-progress', $progress);
		$this->_shm->setVar('task-' . getmypid() . '-message', $message);
		if ($progress== 100) {
			$this->_shm->setVar('done', $this->_shm->getVar('done')+1);
		}

		$message  .= " " . $progress . "%";
		if ($progress == 0) {
			$message = 'Started';
			$this->_executionTime = mktime();
			$this->_log->log("Task (" . getmypid() . "): " . $message, \Zend_Log::INFO);
		} else if ($progress == 100) {
			$message = 'Done';
			$duration = mktime() - $this->_executionTime;
				
			$this->_log->log("Task (" . getmypid() . "): " . $message . ' (' . $duration . ' secs)', \Zend_Log::INFO);
		} else {
			$this->_log->log("Task (" . getmypid() . "): " . $message, \Zend_Log::DEBUG);
		}
	}

	public function triggerGearmanTask($task, $data = array()) {
		return array();
	}
	public function triggerGearmanBackground($task, $data = array()) {
		return true;
	}


}