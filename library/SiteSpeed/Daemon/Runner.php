<?php
/**
 * @package SiteSpeed
 * @subpackage Daemon
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace SiteSpeed\Daemon;

/**
 * 
 * The run object starts the main process of the daemon. The process is forked 
 * for each task manager.
 *
 */
class Runner {
	/**
	 * This variable contains pid manager object
	 * @var Manager $_pidManager
	 */
	protected $_pidManager = null;
	
	/**
	 * Pid reader object
	 * @var File $_pidFile
	 */
	protected $_pidFile = null;
	
	/**
	 * Shared memory object
	 * @var SharedMemory $_shm
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
		$pidFile = \TMP_PATH . '/' . strtolower(str_replace('\\', '-', get_class($this))) . 'd.pid';
		$this->_pidManager = new \SiteSpeed\Daemon\Pid\Manager(getmypid(), $parent);
		$this->_pidFile = new \SiteSpeed\Daemon\Pid\File($pidFile);
		
		$this->_shm = new \SiteSpeed\Daemon\SharedMemory('daemon');
		$this->_shm->setVar('state', 'running');
		
		$this->_initLogSetup();
	}
	
	/**
	 * 
	 * Unset variables at destruct to hopefully free some memory. 
	 */
	public function __destruct() {
//		$this->_shm->setVar('state', 'stopped');
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
	public function log($message, $logLevel = \Zend_Log::DEBUG) {
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
			$log = new \Zend_Log();
		}

		$writerNull = new \Zend_Log_Writer_Null;
		$log->addWriter($writerNull);
		
		$this->_log = $log;
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
		$this->log('Adding log file: ' . $logFile, \Zend_Log::DEBUG);
	}


	/**
	 * 
	 * Adds a manager object to the managers stack
	 * @param Manager\AbstractClass $manager
	 */
	public function addManager(Manager\AbstractClass $manager) {
		return array_push($this->_managers, $manager);
	}

	/**
	 * 
	 * Creates a manager based on the task definition and adds it to the stack.
	 * @param Task\AbstractClass $task
	 */
	public function addManagerByTask(Task\AbstractClass $task) {
		$managerType = $task::getManagerType();
		$managerClass = 'Manager\\' . $managerType;
		if (class_exists($managerClass)) {
			$manager = new $managerClass();
		} else {
			$manager = new \SiteSpeed\Daemon\Manager\Interval();
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
		$this->log("Scanning directory for tasks: " . $dir, \Zend_Log::DEBUG);

		if (!is_dir($dir)) {
			throw new \Exception('Directory does not exists');
		}

		$files = scandir($dir);
		$countLoadedObjects = 0;
		foreach($files as $file) {
			if (preg_match('/(.*)+\.php$/', $file, $match)) {
				require_once($dir . '/' . $file);
				$taskClass = substr(get_class($this), 0, -7) . '\\Task\\' . preg_replace('/\.php$/', '', $file);
				$this->log("Checking task: " . $taskClass, \Zend_Log::DEBUG);
				if (class_exists($taskClass)) {
					$this->log("Adding task: " . $taskClass . ' (' . $taskClass::getManagerType() . ')', \Zend_Log::INFO);
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
		switch ($sig) {
			case SIGTERM:
				// Shutdown
				$this->log('Application (DAEMON) received SIGTERM signal (shutting down)', \Zend_Log::DEBUG);
				exit;
				break;
			case SIGCHLD:
				// Halt
				$this->log('Application (DAEMON) received SIGCHLD signal (halting)', \Zend_Log::DEBUG);		
				while (pcntl_waitpid(-1, $status, WNOHANG) > 0);
				break;
			case SIGINT:
				// Shutdown
				$this->log('Application (DAEMON) received SIGINT signal (shutting down)', \Zend_Log::DEBUG);
				break;
			default:
				$this->log('Application (DAEMON) received ' . $sig . ' signal (unknown action)', \Zend_Log::DEBUG);
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
			$this->log("No daemon tasks found", \Zend_Log::INFO);
			exit;
		}
		$this->log("Starting daemon tasks", \Zend_Log::DEBUG);
		foreach ($this->_managers as $manager) {
			$manager->setLog(clone($this->_log));
			$this->log("Forking manager: "  . get_class($manager), \Zend_Log::INFO);
			$this->_forkManager($manager);
		}
		
		// Default sigHandler
		$this->log("Setting default sighanler", \Zend_Log::DEBUG);
		$this->_sigHandler = new SignalHandler(
			'Main Daemon',
			$this->_log,
			array(&$this, 'sigHandler')
		);
		
		// Write pids to shared memory
		$this->_shm->setVar('childs', $this->_pidManager->getChilds());
	
		// Wait till all childs are done
	    while (pcntl_waitpid(0, $status) != -1) {
        	$status = pcntl_wexitstatus($status);
        	$this->log("Child $status completed");
    	}
		$this->log("Running done.", \Zend_Log::NOTICE);

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
			$this->log('Managers could not be forked!!!', \Zend_Log::CRIT);
			return false;

		} elseif ($pid) {
			// Parent
			$this->_pidManager->addChild($pid);

		} else {
			// Child
			$newPid = getmypid();
			$this->_pidManager->forkChild($newPid);
			$manager->init($this->_pidManager->getParent());
//			
			
			$this->log('Manager forked (PID: ' . $newPid . ') !!!', \Zend_Log::DEBUG);
			$manager->runManager();
			exit;
		}
	}

	public static function getStatus() {
		$pidFile = new \SiteSpeed\Daemon\Pid\File(TMP_PATH . '/sitespeed-daemon-runnerd.pid');
		$pid = $pidFile->readPidFile();

		$status = array('pid' => $pid);
		
		if (file_exists(TMP_PATH . '/daemon.shm')) {
			$shm = new \SiteSpeed\Daemon\SharedMemory('daemon');
			$shmKeys = $shm->getKeys();
			$status['memKeys'] = count($shmKeys); 
			foreach($shm->getKeys() as $key => $value) {
				$status[$key] = $shm->getVar($key);
			}

			// Child info
			if (isset($status['childs'])) {
				foreach($status['childs'] as $child) {
					$status['manager-' . $child] = self::getStatusChild($child);
				}
			}
		}
		

		return $status;
	}
	public static function getStatusChild($childPid) {
		$status = array('childPid' => $childPid);
		
		if (file_exists(TMP_PATH . '/manager-' . $childPid . '.shm')) {
			$shm = new \SiteSpeed\Daemon\SharedMemory('manager-' . $childPid);
			$shmKeys = $shm->getKeys();
			$status['memKeys'] = count($shmKeys); 
			foreach($shm->getKeys() as $key => $value) {
				$status[$key] = $shm->getVar($key);
			}
		}

		return $status;
	}
}