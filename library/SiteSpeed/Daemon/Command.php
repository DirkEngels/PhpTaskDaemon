<?php
/**
 * @package Dew
 * @subpackage Daemon
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace SiteSpeed\Daemon;

/**
* The main Daemon class is responsible for starting, stopping and monitoring
* the daemon. It accepts command line arguments to set daemon runner options.
* The start method creates an instance of Dew_Daemon_Runner and contains the 
* methods for setup a logger, read the config, daemonize the process and set 
* the uid/gui of the process. After that the runner instance starts all the 
* managers.
*/
class Command {

	protected $_consoleOpts;					// Zend_Console_GetOpt instance
	
	/**
	 * 
	 * Daemon run object
	 * @var Runner
	 */
	protected $_runner;

	/**
	 * 
	 * Daemon constructor method
	 * @param Zend_Console_Getopt $consoleOpts
	 */
	public function __construct(Runner $runner = null) {
		// Initialize command line arguments
		$this->setRunner($runner);
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
	 * Returns the daemon runner object
	 * @return Runner
	 */
	public function getRunner() {
		if ($this->_runner === null) {
			$this->_runner = new Runner();
		}
		return $this->_runner;
	}
	
	/**
	 * 
	 * Sets a daemon runner object
	 * @param Runner $runner
	 * @return $this
	 */
	public function setRunner($runner) {
		$this->_runner = $runner;
		return $this;
	}

	/**
	 * 
	 * Reads the command line arguments and invokes the selected action.
	 */
	public function run() {
		// Set action
		$action = ($this->_consoleOpts->getOption('help')) 
			? 'help' 
			: $this->_consoleOpts->getOption('action');
			
		$allActions = array('start', 'stop', 'restart', 'status', 'monitor', 'help');
		if (!in_array($action, $allActions))  {
			$this->help();
			exit;
		}

		if (!$this->_consoleOpts->getOption('verbose')) {
//			$this->getRunner()
//				->getLog()
//				->addFilter(new Zend_Log_Filter_Priority(\Zend_Log::NOTICE));
		}
		if ($this->_consoleOpts->getOption('verbose')) {
			$writerVerbose= new \Zend_Log_Writer_Stream('php://output');
			$this->getRunner()->getLog()->addWriter($writerVerbose);
			$this->getRunner()->getLog()->log('Adding log console', \Zend_Log::DEBUG);
		}
		
		// Perform action
		$this->$action();
		exit;
	}

	/**
	 * 
	 * Action: Start Daemon
	 */
	public function start() {
		$this->getRunner()->start();
	}
	
	/**
	 * 
	 * Action: Stop daemon 
	 */
	public function stop() {
		if (!$this->getRunner()->isRunning()) {
			echo 'Daemon is NOT running!!!' . "\n";
		} else {	
			echo 'Terminating application  !!!' . "\n";
			$this->getRunner()->stop();
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
		
		$status = Runner::getStatus();
		if ($status['pid'] === null) {
			echo "Daemon not running\n";
			exit;
		}
		
		echo "PhpTaskDaemon - Status\n";
		echo "==========================\n";
		echo "\n";
		if (count($status['childs']) == 0) {
			echo "No processes!\n";
		} else {
			echo "Processes (" . count($status['childs']) . ")\n";
		
			foreach ($status['childs'] as $childPid) {
				$managerData = $status['manager-' . $childPid];
				foreach ($managerData as $managerDataKey => $managerDataValue) {
					if (preg_match('/^task-/', $managerDataKey)) {
						echo "  - " . ucfirst($managerData['name']) . " Task\t\t- " . $managerDataValue . "\n";
					}
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
