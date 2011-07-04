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
* The main Daemon class is responsible for starting, stopping and monitoring
* the daemon. It accepts command line arguments to set daemon daemon options.
* The start method creates an instance of Dew_Daemon_Daemon and contains the 
* methods for setup a logger, read the config, daemonize the process and set 
* the uid/gui of the process. After that the daemon instance starts all the 
* managers.
*/
class Console {
	
    /**
     * Configuration Object
     * @var Zend_Config
     */
    protected $_config;
    
	/**
	 * Console options object
	 * @var Zend_Console_Getopt
	 */
	protected $_consoleOpts;
	
	/**
	 * 
	 * Daemon run object
	 * @var Daemon
	 */
	protected $_instance;

	/**
	 * 
	 * Daemon constructor method
	 * @param \Zend_Console_Getopt $consoleOpts
	 */
	public function __construct(Daemon $instance = null) {
		// Initialize command line arguments
		$this->setDaemon($instance);
		$this->setConsoleOpts();
	}
	
	/**
	 * 
	 * Returns an object containing the console arguments.
	 * @return Zend_Console_Getopt
	 */
	public function getConsoleOpts() {
		// Initialize default console options
		if (is_null($this->_consoleOpts)) {
			$this->_consoleOpts = new \Zend_Console_Getopt(
				array(
					'config|c-s'	=> 'Configuration file (defaults: /etc/{name}.conf, {cwd}/{name}.conf)',
					'logfile|l-s'	=> 'Log file (defaults /var/log/{name}.log, {cwd}/{name}.log)',
//					'daemonize|d'	=> 'Run in Daemon mode (default) (fork to background)',
					'action|a=s'	=> 'Action (default: start) (options: start, stop, restart, status, monitor)',
					'list-tasks|lt' => 'List registered tasks',
					'tasks|t'	 	=> 'Include tasks',
//					'exclude-tasks|et=s'	 	=> 'Exclude tasks',
//					'categories|cat' 	=> 'Include categories',
//					'exclude-categories|ecat=s' 	=> 'Exclude categories',
//					'print|p'   	=> 'List Actions',
					'verbose|v'		=> 'Verbose',
					'help|h'		=> 'Show help message (this message)',
				)
			);
		}
		return $this->_consoleOpts;
	}
	
	/**
	 * 
	 * Sets new console arguments
	 * @param Zend_Console_Getopt $consoleOpts
	 * @return $this
	 */
	public function setConsoleOpts(Zend_Console_Getopt $consoleOpts = null) {
		if ($consoleOpts === null) {
			$consoleOpts = $this->getConsoleOpts();
		}
		
		// Parse Options
		try {
			$consoleOpts->parse();
		} catch (Zend_Console_Getopt_Exception $e) {
			echo $e->getUsageMessage();
			exit;
		}
		$this->_consoleOpts = $consoleOpts;

		return $this;
	}
	
	/**
	 * 
	 * Returns the daemon daemon object
	 * @return Daemon
	 */
	public function getDaemon() {
		if ($this->_instance === null) {
			$this->_instance = new Instance();
		}
		return $this->_instance;
	}
	
	/**
	 * 
	 * Sets a daemon daemon object
	 * @param Daemon $instance
	 * @return $this
	 */
	public function setDaemon($instance) {
		$this->_instance = $instance;
		return $this;
	}
	
	/**
	 * 
	 * Gets a config object
	 * @return \Zend_Config
	 */
	public function getConfig() {
		return $this->_config;
	}

	/**
	 * 
	 * Sets a config object
	 * @param \Zend_Config $config
	 * @return $this
	 */
	public function setConfig($config) {
		$this->_config = $config;
	}

	/**
	 * 
	 * Reads the command line arguments and invokes the selected action.
	 */
	public function run() {
		try {
	        // Verbose Output
	        if ($this->_consoleOpts->getOption('verbose')) {
	            $writerVerbose = new \Zend_Log_Writer_Stream('php://output');
	            $this->getDaemon()->getLog()->addWriter($writerVerbose);
	            $this->getDaemon()->getLog()->log('Adding log console', \Zend_Log::DEBUG);
	        }
	
			// Read config
			$configFile = $this->_consoleOpts->getOption('config');
			if (!file_exists($configFile)) {
				$configFile = PROJECT_ROOT . '/etc/config.ini';
			}
	        $this->getDaemon()->getLog()->log('Reading configuration file: ' . $configFile, \Zend_Log::DEBUG);
			try {
				$config = new \Zend_Config_Ini( 
					$configFile
				);
			} catch (Exception $e) {
				echo $e->getMessage();
				exit;
			}

			// Set action
			$action = $this->_consoleOpts->getOption('action');
	
	        if ($this->_consoleOpts->getOption('list-tasks')) {
	            $this->listTasks();
	            exit;
	        }
	
			$allActions = array('start', 'stop', 'restart', 'status', 'monitor', 'help');
			if (!in_array($action, $allActions))  {
				$this->help();
				exit;
			}
	
			// Log File
			$logFile =$this->_consoleOpts->getOption('logfile');
	        if (isset($logFile)) {
	            $writerFile = new \Zend_Log_Writer_Stream($logFile);
	            $this->getDaemon()->getLog()->addWriter($writerFile);
	            $this->getDaemon()->getLog()->log('Adding log file: ' . $logFile, \Zend_Log::DEBUG);
	        }
			
			// Perform action
			$this->$action();
		} catch (\Exception $e) {
			echo 'FATAL EXCEPTION: ' . $e->getMessage();
		}
        echo "\n";
        exit;
	}

	/**
	 * 
	 * Lists the current loaded tasks. 
	 */
    public function listTasks() {
        $tasks = array_merge(
            $this->scanDirectoryForTasks(APPLICATION_PATH . '/Tasks/'),
            $this->scanConfigForTasks(
                $this->_consoleOpts->getOption('config')
            )
        );
        echo "Tasks\n";
        echo "=====\n\n";

        echo "Examples\\Minimal\n";
        echo "-----------------\n";
        echo "\tProcess:\t\tSame\t\t\t(default)\n";
        echo "\tTrigger:\t\tInterval\t\t(default)\n";
            echo "\t- sleepTime:\t\t3\t\t\t(default)\n";
        echo "\tStatus:\t\t\tNone\t\t\t(default)\n";
        echo "\tStatistics:\t\tNone\t\t\t(default)\n";
        echo "\tLogger:\t\t\tNone\t\t\t(default)\n";
        echo "\n";

        echo "Examples\\Parallel\n";
        echo "-----------------\n";
        echo "\tProcess:\t\tParallel\t\t(config)\n";
            echo "\t- maxProcesses:\t\t3\t\t\t(default)\n";
        echo "\tTrigger:\t\tCron\t\t\t(default)\n";
            echo "\t- cronTime:\t\t*/15 * * * *\t\t(default)\n";
        echo "\tStatus:\t\t\tNone\t\t\t(default)\n";
        echo "\tStatistics:\t\tNone\t\t\t(default)\n";
        echo "\tLogger:\t\t\tDataBase\t\t(default)\n";
        echo "\n";
        
        foreach($tasks as $nr => $taskName) {
            echo "- " . $taskName . "\n";
        }
        echo "\n";
        exit;
    }

	/**
	 * 
	 * Scans a directory for task managers and returns the number of loaded
	 * tasks.
	 * 
	 * @param string $dir
	 * @return integer
	 */
	public function scanDirectoryForTasks($dir, $group = null) {
		if (!is_dir($dir . '/' . $group)) {
			throw new \Exception('Directory does not exists');
		}

		$items = scandir($dir . '/' . $group);
		$managers = array();
		$defaultClasses = array('Executor', 'Queue', 'Manager', 'Job');
		foreach($items as $item) {
			if ($item== '.' || $item == '..') { continue; }
			$base = (is_null($group)) ? $item : $group . '/'. $item;
			if (preg_match('/Manager.php$/', $base)) {
				// Try manager file
				echo "Checking manager file: /Tasks/" . $base . "\n";
				if (class_exists(preg_replace('#/#', '\\', 'Tasks/' . substr($base, 0, -4)))) {
					array_push($managers, substr($base, 0, -12));
				}
			} elseif (is_dir($dir . '/' . $base)) {
				// Load recursively
				$managers = array_merge(
					$managers, 
					$this->scanDirectoryForTasks($dir, $base)
				);
			}
		}
		return $managers;
	}
	
	public function scanConfigForTasks($configFile) {
		return array();
	}

	/**
     * Loads a task by name. A task should at least contain an executor object.
     * The manager, job, queue, process, trigger, status and statistics objects
     * are automatically detected. For each object the method checks if the 
     * class has been overloaded or defined in the configuration file. 
     * Otherwise the default object classes will be loaded. The default objects
     * can also be defined using the configuration file.
     * 
     * @param string $taskName The name of the task
     * @return \PhpTaskDaemon\Task\Manager\AbstractClass 
	 */
	public function loadTask($taskName) {
		
	}

	/**
	 * 
	 * Action: Start Daemon
	 */
	public function start() {
		$managers = $this->scanDirectoryForTasks(PROJECT_ROOT . '/app/Tasks/');
		echo "\n";
		echo "\n";
		foreach($managers as $manager) {
			$this->getDaemon()->loadManagerByName($manager);
            echo $manager . "\n";
		}
//		$this->getDaemon()->loadManagerByName('Concept/PocTask');
		$this->getDaemon()->start();
	}
	
	public function log($message, $other) {
		echo $message . "\n";
	}

	/**
	 * 
	 * Action: Stop daemon 
	 */
	public function stop() {
		if (!$this->getDaemon()->isRunning()) {
			echo 'Daemon is NOT running!!!' . "\n";
		} else {	
			echo 'Terminating application  !!!' . "\n";
			$this->getDaemon()->stop();
		}

		exit();
	}
	
	/**
	 * Alias for stopping and restarting the daemon.
	 */
	public function restart() {
		$this->stop();
		$this->start();
	}
	
	/**
	 * 
	 * Action: Get daemon status
	 */
	public function status() {
		
		$status = State::getState();
		if ($status['pid'] === null) {
			echo "Daemon not running\n";
			exit;
		}
		echo var_dump($status);

		echo "PhpTaskDaemon - Status\n";
		echo "==========================\n";
		echo "\n";
		if (count($status['childs']) == 0) {
			echo "No processes!\n";
		} else {
			echo "Processes (" . count($status['childs']) . ")\n";

			foreach ($status['childs'] as $childPid) {
				$managerData = $status['task-' . $childPid];
				echo " - [" . $childPid . "]: " . $status['status-' . $childPid] . "\t(Queued: " . $managerData['statistics']['queued'] . "\tDone: " . $managerData['statistics']['done'] . "\tFailed:" . $managerData['statistics']['failed'] . ")\n";
				echo "  - [" . $childPid . "]: (" . $managerData['status']['percentage'] . ") => " . $managerData['status']['message'] . "\n";
			}

		}
		return true;
	}

	/**
	 * 
	 * Displays the current tasks and activities of the daemon. The monitor 
	 * action refreshes every x milliseconds.
	 */
	public function monitor() {
		$out  = "PhpTaskDaemon - Monitoring\n" .
				"==========================\n";
		echo "Function not yet implemented\n";
	}

	/**
	 * 
	 * Displays a help message containing usage instructions.
	 */
	public function help() {
		echo $this->_consoleOpts->getUsageMessage();
		exit;
	}
	
}
