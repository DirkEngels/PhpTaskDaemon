<?php

/**
 * 
 * This is the abstract class of a Daemon_Manager. It provides the basic 
 * methods needed for almost all managers. 
 * 
 * @author DirkEngels <d.engels@dirkengels.com>
 *
 */
abstract class Dew_Daemon_Manager_Abstract {

	/** 
	 * 
	 * Manager Type: Tasks are executed by a managers. The manager decide when
	 * and how to run tasks. The following standard managers are available.
	 * 
	 * - Interval:	This managers loads the queue and executes the tasks 
	 * 				sequentially. After finishing all tasks the managers waits 
	 * 				for an adjustable interval.
	 * - Cron:		The cron manager is similar to the interval managers, 
	 * 				except after finishing it waits for a predefined time in 
	 * 				stead of an interval.
	 * - Forked:	This manager loads a queue and forks the process one or 
	 * 				more times to execute a set of tasks in parallel. The 
	 * 				managers forks the process to execute a single task. The 
	 * 				maximum of forked processes at a single time can be 
	 * 				controlled by the manager.
	 * - Gearman:	The gearman manager starts one or more gearman workers. It
	 * 				re-uses the forked manager and extend the execute method by
	 * 				starting a gearman worker.
	 */
	const PROCESS_TYPE_INTERVAL = 'interval';
	const PROCESS_TYPE_CRON = 'cron';
	const PROCESS_TYPE_GEARMAN = 'gearman';
	const PROCESS_TYPE_FORKED = 'forked';

	/**
	 * Task object
	 * @var Dew_Daemon_Task_Abstract
	 */
	protected $_task = null;	
	
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
	 * @var Dew_Daemon_Pid_Manager
	 */
	protected $_pidManager = null;

	/**
	 * 
	 * Shared memory object
	 * @var Dew_Daemon_Shm
	 */
	protected $_shm = null;
	
	/**
	 * Manager Queue
	 * @var array
	 */
	protected $_queue = array();
	
	/**
	 * Time to wait in milliseconds before the next run.
	 * 
	 * @var integer
	 */
	protected $_waitTime = 10;


	/**
	 * 
	 * The pid reader constructor has one optional argument containing a 
	 * filename.
	 * @param string $filename
	 */
	public function __construct($parentPid = null) {
		$this->_pidManager = new Dew_Daemon_Pid_Manager(
			getmypid(), 
			$parentPid
		);
		
		$this->_shm= new Dew_Daemon_SharedMemory(
			'task-' . $this->_pidManager->getCurrent()
		);
	}
	public function __destruct() {
		echo 'Shutting down manager: ' . get_class($this) . "\n";
//		echo var_dump($this->_shm);
//		$this->_shm->remove();
		unset($this->_shm);
	}

	/**
	 * 
	 * Returns the current loaded task object.
	 * @return Dew_Daemon_Task_Abstract
	 */
	public function getTask() {
		return $this->_task;
	}

	/**
	 * 
	 * Sets the current task object to run.
	 * @param Dew_Daemon_Task_Abstract $task
	 * @return $this
	 */
	public function setTask($task) {
		if (is_a($task, 'Dew_Daemon_Task_Abstract')) {
			$this->_task = $task;
		}
		return $this;
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
	 * Returns the shared memory object
	 * @return Dew_Daemon_Shm
	 */
	public function getShm() {
		return $this->_shm;
	}

	/**
	 * 
	 * Sets a shared memory object
	 * @param Dew_Daemon_Shm $shm
	 */
	public function setShm(Dew_Daemon_Shm $shm) {
		$this->_shm = $shm;
	}

	/**
	 * 
	 * Returns the current loaded queue array
	 * @return array
	 */
	public function getQueue() {
		return $this->_queue;
	}
	
	/**
	 * 
	 * Sets the current queue to process.
	 * @param array $queue
	 * @return $this
	 */
	public function setQueue($queue) {
		if (is_array($queue)) {
			$this->_queue = $queue;
		}
		return $this;
	}

	public function runManager() {
		// Override signal handler
		echo "Overriding SIGHANDLER\n\n";
		$this->_sigHandler = new Dew_Daemon_SignalHandler(
			get_class($this),
			$this->_log, 
			array(&$this, 'sigHandler')
		);

		if ($this->getTask()->getLog() === null) {
			$this->getTask()->setLog($this->_log);
		}
		if ($this->getTask()->getPidManager() === null) {
			$this->getTask()->setPidManager($this->_pidManager);
		}
		
		$this->executeManager();
	}

	/**
	 * Checks the sanity of the manager input data.
	 * 
	 * @param array $inputData
	 * @return boolean
	 */
	protected function _checkTaskInput($inputData) {
		// Verify the input is an array
		if (!is_array($inputData)) {
			return false;
		}
		// Check if the needed inputFields exist
		foreach($this->_inputFields as $field) {
			if (!isset($inputData[$field])) {
				return false;
			}
		}
		// All OK!
		return true;
	}
	
	/**
	 * 
	 * Displays the status of a running task, which is retreived from the 
	 * shared memory segments registered by the daemon and its managers.
	 * @return string
	 */
	public function showStatus() {
		$out = "[" . $this->_pidManager->getCurrent() . "] " . get_class($this) . " (" . $this->_task->getManagerType() . ") :\n";

		if ($this->_pidManager->hasChilds()) {
			$childs = $this->_pidManager->getChilds();
			foreach($childs as $child) {
				echo $this->_shm->getVar($child);
			}
		}
		
		return $out;
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
				$this->_log->log('Application (TASK) received SIGTERM signal (shutting down)', Zend_Log::DEBUG);
				break;
			case SIGCHLD:
				// Halt
				$this->_log->log('Application (TASK) received SIGCHLD signal (halting)', Zend_Log::DEBUG);		
				while (pcntl_waitpid(-1, $status, WNOHANG) > 0);
				break;
			case SIGINT:
				// Shutdown
				$this->_log->log('Application (TASK) received SIGINT signal (shutting down)', Zend_Log::DEBUG);
				$this->_shm->remove();
//				exit;
				break;
			default:
				$this->_log->log('Application (TASK) received ' . $sig . ' signal (unknown action)', Zend_Log::DEBUG);
				break;
		}
		echo "sighandler Task done\n\n";
		exit;
	}
	
}