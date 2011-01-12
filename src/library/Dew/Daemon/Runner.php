<?php
/**
 * @package Dew
 * @subpackage Daemon
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

/**
 * 
 * The run object starts the main process of the daemon. The process is forked 
 * for each task manager.
 *
 */
class Dew_Daemon_Runner {
	/**
	 * This variable contains pid manager object
	 * @var Dew_Daemon_Pid_Manager $_pidManager
	 */
	protected $_pidManager = null;
	
	/**
	 * Pid reader object
	 * @var Dew_Daemon_Pid_File $_pidFile
	 */
	protected $_pidFile = null;
	
	/**
	 * Shared memory object
	 * @var Dew_Daemon_SharedMemory $_shm
	 */
	protected $_shm = null;
	
	/**
	 * Logger object
	 * @var Zend_Log $_log
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
		$pidFile = TMP_PATH . '/' . strtolower(get_class($this)) . 'd.pid';
		$this->_pidManager = new Dew_Daemon_Pid_Manager(getmypid(), $parent);
		$this->_pidFile = new Dew_Daemon_Pid_File($pidFile);
		
		$this->_shm = new Dew_Daemon_SharedMemory('daemon');
		$this->_shm->setVar('state', 'running');
		
		$this->_initLogSetup();
	}
	
	/**
	 * 
	 * Unset variables at destruct to hopefully free some memory. 
	 */
	public function __destruct() {
//		$this->_shm->setVar('state', 'stopped');
//		echo var_dump($this->_pidManager);
		unset($this->_pidManager);
		unset($this->_pidFile);
		unset($this->_shm);
	}

	/**
	 * 
	 * Logs a message to the Zend_Log object. This method can be used to hook 
	 * in custom log actions. 
	 * @param string $message
	 * @param int $logLevel
	 */
	public function log($message, $logLevel = Zend_Log::DEBUG) {
		$this->_log->log('(' . getmypid() . ') ' . $message, $logLevel);
	}

	/**
	 * 
	 * Returns the log file to use. It tries a few possible options for
	 * retrieving or composing a valid logfile.
	 * @return string
	 */
	protected function _getLogFile() {
		$logFile = TMP_PATH . '/' . strtolower(get_class($this)) . 'd.log';
		return $logFile;
	}

	/**
	 *
	 * Initialize logger with a null writer. Null writer is needed because this function
	 * is invoked very early in the bootstrap.
	 * 
	 * @param Zend_Log $log
	 */
	protected function _initLogSetup(Zend_Log $log = null) {
		if (is_null($log)) {
			$log = new Zend_Log();
		}

		$writerNull = new Zend_Log_Writer_Null;
		$log->addWriter($writerNull);
		
		$this->_log = $log;
	}

	/**
	 * 
	 * Add additional (zend) log writers
	 */
	protected function _initLogOutput() {
		// Add writer: verbose
//		if ($this->_consoleOpts->getOption('verbose')) {
			$writerVerbose= new Zend_Log_Writer_Stream('php://output');
			$this->_log->addWriter($writerVerbose);
			$this->log('Adding log console', Zend_Log::DEBUG);
//		}
		
		$logFile = $this->_getLogFile();
		if (!file_exists($logFile)) {
			touch($logFile);
		}
		
		$writerFile = new Zend_Log_Writer_Stream($logFile);
		$this->_log->addWriter($writerFile);
		$this->log('Adding log file: ' . $logFile, Zend_Log::DEBUG);
	}


	/**
	 * 
	 * Adds a manager object to the managers stack
	 * @param Dew_Daemon_Manager_Abstract $manager
	 */
	public function addManager(Dew_Daemon_Manager_Abstract $manager) {
		return array_push($this->_managers, $manager);
	}

	/**
	 * 
	 * Creates a manager based on the task definition and adds it to the stack.
	 * @param Dew_Daemon_Task_Abstract $task
	 */
	public function addManagerByTask(Dew_Daemon_Task_Abstract $task) {
		$managerType = $task::getManagerType();
		$managerClass = 'Dew_Daemon_Manager_' . $managerType;
		if (class_exists($managerClass)) {
			$manager = new $managerClass();
		} else {
			$manager = new Dew_Daemon_Manager_Interval();
		}
		
		$manager->setTask($task);
		$this->addManager($manager);
		return $this;
	}

	/**
	 * 
	 * Scans a directory for task managers
	 * 
	 * @param string $dir
	 */
	public function scanTaskDirectory($dir) {
		$this->log("Scanning directory for tasks: " . $dir, Zend_Log::DEBUG);

		if (!is_dir($dir)) {
			throw new Exception('Directory does not exists');
		}

		$files = scandir($dir);
		$countLoadedObjects = 0;
		foreach($files as $file) {
			if (preg_match('/(.*)+\.php$/', $file, $match)) {
				require_once($dir . '/' . $file);
				$taskClass = substr(get_class($this), 0, -7) . '_Task_' . preg_replace('/\.php$/', '', $file);
				$this->log("Checking task: " . $taskClass, Zend_Log::DEBUG);
				if (class_exists($taskClass)) {
					$this->log("Adding task: " . $taskClass . ' (' . $taskClass::getManagerType() . ')', Zend_Log::INFO);
					$task = new $taskClass();
					$this->addManagerByTask($task);
					$countLoadedObjects++;
				}
			}
		}
		return $countLoadedObjects;
	}
	
	/**
	 * 
	 * This is the public start function of the daemon. It checks the input and
	 * available managers before running the daemon.
	 */
	public function start() {
		$this->_pidFile->writePidFile($this->_pidManager->getCurrent());
		$this->_initLogOutput();

		// Check input here
		$this->scanTaskDirectory(APPLICATION_PATH . '/daemon/');
		
		// All valid
		$this->_run();
	}

	/**
	 * 
	 * POSIX Signal handler callback
	 * @param $sig
	 */
	public function sigHandler($sig) {
		echo "GOT SIGNAL "  . $sig . "\n\n\n";
		switch ($sig) {
			case SIGTERM:
				// Shutdown
				$this->log('Application (DAEMON) received SIGTERM signal (shutting down)', Zend_Log::DEBUG);
				exit;
				break;
			case SIGCHLD:
				// Halt
				$this->log('Application (DAEMON) received SIGCHLD signal (halting)', Zend_Log::DEBUG);		
				while (pcntl_waitpid(-1, $status, WNOHANG) > 0);
				break;
			case SIGINT:
				// Shutdown
//				$this->log('Application (DAEMON) received SIGINT signal (shutting down)', Zend_Log::DEBUG);
//				$this->_pidFile->unlinkPidFile();
//				$this->_shm->remove();
				break;
			default:
				$this->log('Application (DAEMON) received ' . $sig . ' signal (unknown action)', Zend_Log::DEBUG);
				//exit;
				break;
		}
		echo "sighandler Runner done\n\n";
	}

	/**
	 * 
	 * This is the main function for running the daemon!
	 */
	protected function _run() {
		// All OK.. Let's go
		declare(ticks = 1);

		if (count($this->_managers)==0) {
			$this->log("No daemon tasks found", Zend_Log::INFO);
			exit;
		}
		$this->log("Starting daemon tasks", Zend_Log::DEBUG);
		foreach ($this->_managers as $manager) {
//			$manager->setLog($this->_log);
			$manager->setLog(clone($this->_log));
			$this->log("Forking manager: "  . get_class($manager), Zend_Log::INFO);
			$this->_forkManager($manager);
		}
		// Default sigHandler
		$this->log("Setting default sighanler", Zend_Log::DEBUG);
		$this->_sigHandler = new Dew_Daemon_SignalHandler(
			'Main Daemon',
			$this->_log,
			array(&$this, 'sigHandler')
		);
	
		// Wait till all childs are done
	    while (pcntl_waitpid(0, $status) != -1) {
        	$status = pcntl_wexitstatus($status);
        	$this->log("Child $status completed");
    	}
		$this->log("Running done.", Zend_Log::NOTICE);

		$this->_pidFile->unlinkPidFile();
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
			$this->log('Managers could not be forked!!!', Zend_Log::CRIT);
			return false;

		} elseif ($pid) {
			// Parent
			$this->_pidManager->addChild($pid);

		} else {
			// Child
			$newPid = posix_getpid();
			$this->_pidManager->forkChild($newPid);
			
			$this->log('Manager forked (PID: ' . $newPid . ') !!!', Zend_Log::DEBUG);
			$manager->runManager();
			exit;
		}
	}

}