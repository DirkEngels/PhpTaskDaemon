<?php
/**
 * @author DirkEngels <d.engels@dirkengels.com>
 * @package Dew
 * @subpackage Dew_Daemon
 */

/**
 * 
 * Tasks are an important function of the application. It provides an api for
 * defining task by extending this abstract class and implementing two methods
 * as defined in the interface. The task can then be executed by a manager,
 * which determines when and how to run the task.
 * 
 */
class Dew_Daemon_Task_Abstract {

	/**
	 * 
	 * This variable needs to contain one of the defined manager types. 
	 * @var string
	 */
	static protected $_managerType = Dew_Daemon_Manager_Abstract::PROCESS_TYPE_INTERVAL;
	
	
	/**
	 * 
	 * Pid manager object. This class is repsonsible for storing the current, 
	 * parent and child process IDs.
	 * @var Dew_Daemon_Pid_Manager
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
	 * @var Dew_Daemon_Shm
	 */
	protected $_shm = null;
	
	/**
	 * This name of the task, which will be extracted from the class name.
	 * @var string
	 */
	protected $_name = '';
	
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
	 * @param Zend_Log $log
	 * @param Dew_Daemon_Pid_Manager $pidManager
	 * @param Dew_Daemon_Shm $shm
	 */
	public function __construct($log = null, $pidManager = null, $shm = null) {
		if (is_a($log, 'Zend_Log')) {
			$this->setLog($log);
		}
		if (is_a($pidManager, 'Dew_Daemon_Pid_Manager')) {
			$this->setPidManager($pidManager);
		}
		if (is_a($shm, 'Dew_Daemon_Shm')) {
			$this->setShm($shm);
		} else {
			$this->_shm = new Dew_Daemon_Shm(getmypid());
		}
		$this->init();
		$sigHandler = new Dew_Daemon_Signals($this->_log);
	}
	
	/**
	 * 
	 * Clean up objects to free memory.
	 */
	public function __destruct() {
		unset($this->_shm);
		unset($this->_pidManager);
	}

	/**
	 * 
	 * Initializes a task process by registering its name, pid and shared 
	 * memory segment.
	 * @param string $name
	 */
	public function init($name = null) {
		if ($name === null) {
			$name = preg_replace('/^Dew_Daemon_Task_/', '', get_class($this));
		}
		$this->_name = $name;
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
	public function setLog(Zend_Log $log) {
		$this->_log = $log;
		return $this;
	}
	
	/**
	 * 
	 * Returns the pid manager of the task manager
	 * @return Dew_Daemon_Pid_Manager
	 */
	public function getPidManager() {
		return $this->_pidManager;
	}
	
	/**
	 * 
	 * Sets the pid manager of the task manager
	 * @param Dew_Daemon_Pid_Manager $pidManager
	 * @return $this
	 */
	public function setPidManager(Dew_Daemon_Pid_Manager $pidManager) {
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
	public function updateMemoryQueue($count) {
		echo "Sys (" . getmypid() . "): Queue: " . $count . "\n";
		$this->_shm->setVar(1, $count);
	}

	/**
	 * 
	 * Updates the shared memory about the current task progress
	 * @param int $progress
	 * @param string $message
	 */
	public function updateMemoryTask($progress, $message = '') {
		$message  .= " " . $progress . "%";
		if ($progress == 0) {
			$message = 'Started';
			$this->_executionTime = mktime();
			$this->_log->log("Task (" . getmypid() . "): " . $message, Zend_Log::DEBUG);
		}
		if ($progress == 100) {
			$message = 'Done';
			$duration = mktime() - $this->_executionTime;
			$this->_log->log("Task (" . getmypid() . "): " . $message . ' (' . $duration . ' secs)', Zend_Log::DEBUG);
		} 
		
		echo "Task (" . getmypid() . "): " . $message . "\n";
		$this->_shm->setVar(getmypid(), $message);	
	}
}