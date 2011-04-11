<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon;

/**
 * 
 * The run object starts the main process of the daemon. The process is forked 
 * for each task manager.
 *
 */
class Instance {
	/**
	 * This variable contains pid manager object
	 * @var Pid\Manager $_pidManager
	 */
	protected $_pidManager = null;
	
	/**
	 * Pid reader object
	 * @var Pid\File $_pidFile
	 */
	protected $_pidFile = null;
	
	/**
	 * Shared memory object
	 * @var Ipc\SharedMemory $_shm
	 */
	protected $_shm = null;
	
	/**
	 * Logger object
	 * @var \Zend_Log $_log
	 */
	protected $_log = null;
	
	/**
	 * Array with managers
	 * @var array $_managers
	 */
	protected $_managers = array();
	
	/**
	 * 
	 * The construction has one optional argument containing the parent process
	 * ID. 
	 * @param int $parent
	 */
	public function __construct($parent = null) {
		$pidFile = \TMP_PATH . '/phptaskdaemond.pid';
		$this->_pidManager = new \PhpTaskDaemon\Daemon\Pid\Manager(getmypid(), $parent);
		$this->_pidFile = new \PhpTaskDaemon\Daemon\Pid\File($pidFile);
		
		$this->_shm = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory('phptaskdaemond');
		
		$this->_initLogSetup();
	}
	
	/**
	 * 
	 * Unset variables at destruct to hopefully free some memory. 
	 */
	public function __destruct() {
		unset($this->_pidManager);
		unset($this->_pidFile);
		unset($this->_shm);
	}

	/**
	 * 
	 * Returns the log object
	 * @return \Zend_Log
	 */
	public function getLog() {
		return $this->_log;
	}

	/**
	 * 
	 * Sets the log object
	 * @param \Zend_Log $log
	 * @return $this
	 */
	public function setLog(Zend_Log $log) {
		$this->_log = $log;
		return $this;
	}

	/**
	 * 
	 * Returns the log file to use. It tries a few possible options for
	 * retrieving or composing a valid logfile.
	 * @return string
	 */
	protected function _getLogFile() {
		$logFile = TMP_PATH . '/phptaskdaemond.log';
		return $logFile;
	}

	/**
	 *
	 * Initialize logger with a null writer. Null writer is needed because this function
	 * is invoked very early in the bootstrap.
	 * 
	 * @param \Zend_Log $log
	 */
	protected function _initLogSetup(Zend_Log $log = null) {
		if (is_null($log)) {
			$log = new \Zend_Log();
		}

		$writerNull = new \Zend_Log_Writer_Null;
		$log->addWriter($writerNull);
		
		$this->_log = $log;
	}

	/**
	 * 
	 * Add additional (zend) log writers
	 */
	protected function _initLogOutput() {
		// Add writer: verbose		
		$logFile = $this->_getLogFile();
		if (!file_exists($logFile)) {
			touch($logFile);
		}
		
		$writerFile = new \Zend_Log_Writer_Stream($logFile);
		$this->_log->addWriter($writerFile);
		$this->_log->log('Adding log file: ' . $logFile, \Zend_Log::DEBUG);
	}

	/**
	 * 
	 * Return the loaded manager objects.
	 * @return array
	 */
	public function getManagers() {
		return $this->_managers;
	}

	/**
	 * 
	 * Adds a manager object to the managers stack
	 * @param \PhpTaskDaemon\Task\Manager\AbstractClass $manager
	 * @return bool
	 */
	public function addManager($manager) {
		return array_push($this->_managers, $manager);
	}

	/**
	 * 
	 * Scans a directory for task managers and returns the number of loaded
	 * tasks.
	 * 
	 * @param string $dir
	 * @return integer
	 */
	public function scanTaskDirectory($dir) {
		$this->_log->log("Scanning directory for tasks: " . $dir, \Zend_Log::DEBUG);

		if (!is_dir($dir)) {
			throw new \Exception('Directory does not exists');
		}

		$groups = scandir($dir);
		$tasks = 0;
		$countLoadedObjects = 0;
		foreach($groups as $group) {
			if ($group == '.' || $group == '..') { continue; }

			$this->_log->log("Scanning dir: " . $dir . $group, \Zend_Log::INFO);
			$tasks = scandir($dir . '/' . $group);
			foreach ($tasks as $task) {
				if ($task == '.' || $task == '..') { continue; }

				$this->_log->log("Found: " . $group . "\\" . $task, \Zend_Log::INFO);
				$this->loadTaskByName($group . '\\' . $task);			
			}
		}
		return $countLoadedObjects;
	}
	
	/**
	 * 
	 * Try loading a task by name
	 * @param string $taskName
	 * @return \PhpTaskDaemon\Task\Manager\AbstractClass
	 */
	public function loadTaskByName($taskName) {
		$managerClass = '\\Tasks\\' . $taskName . '\\Manager'; 
		$queueClass = '\\Tasks\\' . $taskName . '\\Queue';
		$executorClass = '\\Tasks\\' . $taskName . '\\Executor';

		//Queue
		$queue = (class_exists($queueClass)) 
			? new $queueClass()
			: new \PhpTaskDaemon\Task\Queue\BaseClass();
		
		// Executuor
		$executor = (class_exists($executorClass)) 
			? new $executorClass()
			: new \PhpTaskDaemon\Task\Executor\BaseClass();

		// Manager
		$manager = (class_exists($managerClass)) 
			? new $managerClass($executor, $queue)
			: new \PhpTaskDaemon\Task\Manager\Interval($executor, $queue);
		$this->addManager($manager);

		return $manager;
	}

	/**
	 * 
	 * This is the public start function of the daemon. It checks the input and
	 * available managers before running the daemon.
	 */
	public function start() {
		$this->_pidFile->write($this->_pidManager->getCurrent());
		$this->_initLogOutput();

		// Check input here
		$this->scanTaskDirectory(APPLICATION_PATH . '/Tasks/');
		
		// All valid
		$this->_run();
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
				$this->_log->log('Application (DAEMON) received SIGTERM signal (shutting down)', \Zend_Log::DEBUG);
				exit;
				break;
			case SIGCHLD:
				// Halt
				$this->_log->log('Application (DAEMON) received SIGCHLD signal (halting)', \Zend_Log::DEBUG);		
				while (pcntl_waitpid(-1, $status, WNOHANG) > 0);
				break;
			case SIGINT:
				// Shutdown
				$this->_log->log('Application (DAEMON) received SIGINT signal (shutting down)', \Zend_Log::DEBUG);
				break;
			default:
				$this->_log->log('Application (DAEMON) received ' . $sig . ' signal (unknown action)', \Zend_Log::DEBUG);
				break;
		}
	}

	/**
	 * 
	 * This is the main function for running the daemon!
	 */
	protected function _run() {
		// All OK.. Let's go
		declare(ticks = 1);

		if (count($this->_managers)==0) {
			$this->_log->log("No daemon tasks found", \Zend_Log::INFO);
			exit;
		}
		$this->_shm->setVar('childs', array());
		$this->_log->log("Starting daemon tasks", \Zend_Log::DEBUG);
		foreach ($this->_managers as $manager) {
			$manager->setLog(clone($this->_log));
			$this->_log->log("Forking manager: "  . get_class($manager), \Zend_Log::INFO);
			$this->_forkManager($manager);
		}
		
		// Default sigHandler
		$this->_log->log("Setting default signal interrupt handler", \Zend_Log::DEBUG);
		$this->_sigHandler = new Interrupt\Signal(
			'Main Daemon',
			$this->_log,
			array(&$this, 'sigHandler')
		);
		
		// Write pids to shared memory
		$this->_shm->setVar('childs', $this->_pidManager->getChilds());
	
		// Wait till all childs are done
	    while (pcntl_waitpid(0, $status) != -1) {
        	$status = pcntl_wexitstatus($status);
        	$this->_log->log("Child $status completed", \Zend_Log::NOTICE);
    	}
		$this->_log->log("Running done.", \Zend_Log::NOTICE);

		$this->_pidFile->unlink();
		$this->_shm->remove();

		exit;
	}

	/**
	 * 
	 * Fork the managers to be processed in the background. The foreground task
	 * is used to add gearman tasks to the queue for the background gearman 
	 * managers
	 */
	private function _forkManager($manager)
	{
		$pid = pcntl_fork();
		if ($pid === -1) {
			// Error
			$this->_log->log('Managers could not be forked!!!', \Zend_Log::CRIT);
			return false;

		} elseif ($pid) {
			// Parent
			$this->_pidManager->addChild($pid);
			$managerName = substr(substr(get_class($manager), 6), 0, -8);
			$this->_shm->setVar('status-'. $pid, $managerName);
			

		} else { 
			$newPid = getmypid();
			$this->_pidManager->forkChild($newPid);
			$manager->init($this->_pidManager->getParent());

			$statistics = new \PhpTaskDaemon\Task\Queue\Statistics\BaseClass();
			$manager->getQueue()->setStatistics($statistics);
			
			$status = new \PhpTaskDaemon\Task\Executor\Status\BaseClass();
			$manager->getExecutor()->setStatus($status);
			
			$this->_log->log('Manager forked (PID: ' . $newPid . ') !!!', \Zend_Log::DEBUG);
			$manager->runManager();
			exit;
		}
	}

}