<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Core
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
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
	 * @param Zend_Console_Getopt $consoleOpts
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
					'daemonize|d'	=> 'Run in Daemon mode (default) (fork to background)',
					'action|a=s'	=> 'Action (default: start) (options: start, stop, restart, status, monitor)',
					'print|p'   => 'List Actions',
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
	 * Reads the command line arguments and invokes the selected action.
	 */
	public function run() {
		// Set action
		$action = $this->_consoleOpts->getOption('action');

        if ($this->_consoleOpts->getOption('print')) {
            $this->listActions();
            exit;
        }

		$allActions = array('start', 'stop', 'restart', 'status', 'monitor', 'help');
		if (!in_array($action, $allActions))  {
			$this->help();
			exit;
		}

		if ($this->_consoleOpts->getOption('verbose')) {
			$writerVerbose = new \Zend_Log_Writer_Stream('php://output');
			$this->getDaemon()->getLog()->addWriter($writerVerbose);
			$this->getDaemon()->getLog()->log('Adding log console', \Zend_Log::DEBUG);
		}
		
		// Perform action
		$this->$action();
		exit;
	}

    public function listActions() {
        $this->getDaemon()->scanTaskDirectory(APPLICATION_PATH . '/Tasks/');
        $tasks = $this->getDaemon()->getManagers();
        echo "Tasks: \n";
        foreach($tasks as $task) {
            $taskName = get_class($task);
            $taskName = substr($taskName, 6);
            $taskName = substr($taskName, 0, -8);
            echo "- " . $taskName . "\n";
        }
        echo "\n";
        exit;
    }

	/**
	 * 
	 * Action: Start Daemon
	 */
	public function start() {
		$this->getDaemon()->start();
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

echo "
















































";
		echo "PhpTaskDaemon - Status\n";
		echo "==========================\n";
		echo "\n";
		if (count($status['childs']) == 0) {
			echo "No processes!\n";
		} else {
			echo "Processes (" . count($status['childs']) . ")\n";

		
			foreach ($status['childs'] as $childPid) {
				$managerData = $status['task-' . $childPid];
				echo " - [" . $childPid . "]: " . $status['status-' . $childPid] . "\t(Queued: " . $managerData['statistics']['Queued'] . "\tDone: " . $managerData['statistics']['Done'] . "\tFailed:" . $managerData['statistics']['Failed'] . ")\n";
				echo "  - [" . $childPid . "]: (" . $managerData['status']['percentage'] . ") => " . $managerData['status']['message'] . "\n";
//				echo var_dump($managerData['status']);
				foreach ($managerData as $managerDataKey => $managerDataValue) {
//					if (preg_match('/^task-/', $managerDataKey)) {
						
//					}
				}
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
