<?php
/**
 * @package Dew
 * @subpackage Daemon
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

/**
* The main Daemon class is responsible for starting, stopping and monitoring
* the daemon. It accepts command line arguments to set daemon runner options.
* The start method creates an instance of Dew_Daemon_Runner and contains the 
* methods for setup a logger, read the config, daemonize the process and set 
* the uid/gui of the process. After that the runner instance starts all the 
* managers.
*/
class Dew_Daemon_Command {

	protected $_consoleOpts;					// Zend_Console_GetOpt instance
	
	/**
	 * 
	 * Daemon run object
	 * @var Dew_Daemon_Runner
	 */
	protected $_runner;

	/**
	 * 
	 * Daemon constructor method
	 * @param Zend_Console_Getopt $consoleOpts
	 */
	public function __construct(Dew_Daemon_Runner $runner = null) {
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
			$this->_consoleOpts = new Zend_Console_Getopt(
				array(
					'config|c-s'	=> 'Configuration file (defaults: /etc/{name}.conf, {cwd}/{name}.conf)',
					'logfile|l-s'	=> 'Log file (defaults /var/log/{name}.log, {cwd}/{name}.log)',
					'daemonize|d'	=> 'Run in Daemon mode (default) (fork to background)',
					'single|s'		=> 'Run single tick (default: no) (no gearman workers are started)',
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
	 * @return Dew_Daemon_Runnerner
	 */
	public function getRunner() {
		if ($this->_runner === null) {
			$this->_runner = new Dew_Daemon_Runner();
		}
		return $this->_runner;
	}
	
	/**
	 * 
	 * Sets a daemon runner object
	 * @param Dew_Daemon_Runnerner $runner
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
//		if (!$this->_isRunning()) {
//			echo 'Application (' . $this->_name . ') is NOT running!!!' . "\n";
//		} else {
		$pidFile = new Dew_Daemon_Pid_File(TMP_PATH . '/dew_daemon_rund.pid');
//		echo ;
//		exit;
		$shm = new Dew_Daemon_Shm('daemon');

		echo 'Getting status of daemon (PID: ' . $pidFile->readPidFile() . ') !!!' . "\n";		
		
		foreach($shm->getKeys() as $key) {
			echo $key . ' => ' . $shm->getVar($key) . "\n";
		}
		
		exit();
	}
	
	/**
	 * 
	 * Displays the current tasks and activities of the daemon. The monitor 
	 * action refreshes every x milliseconds.
	 */
	public function monitor() {
		$out  = "PhpTaskDaemon - Monitoring\n" .
				"==========================\n" .
				"System\n";
		
echo "
PhpTaskDaemon - Monitoring
==========================
System
- Memory:			12.42 Mb	Max:	28.32 Mb
- Load average:			0.03 (1 min) 	0.11 (5 min)	0.54 (15 min)

Processes
- Sleep Manager	(I)		Queued: 23	Done: 1.234	State: running
  - Sleep Task			- Running: 70%
- Sleep2 Manager (I)		Queued: 0	Done: 2.434	State: wait till 14:04:03
- Sleep Manager (C)		Queued: 0	Done:   574	State: wait till 14:05:05
- Sleep Manager (G)		Queued: 345	Done: 3.123	State: 1 of 2 workers active
  - Sleep Worker		- Running: 50%
  - Sleep Worker		- Waiting for job
- Sleep Manager (F)		Queue:  10	Done: 9.231	State: 4 of 4 childs active
  - Sleep Task			- Running: 50%
  - Sleep Task			- Running: 20%
  - Sleep Task			- Running: 80%
  - Sleep Task			- Running: 10%
";
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
