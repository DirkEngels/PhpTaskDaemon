<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Interrupt
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon\Interrupt;

/**
* The Daemon Signals object can be encapsulated by other classes to 
* enable posix signal handlers. These are needed for triggering the cleaning up
* the daemon, managers and possible active tasks when receiving a posix signal.
* A callback can be provided to run a method related to the current process. 
* 
*/
class Signal {

	/**
	 * Task identifier
	 * @var string|null 
	 */
	protected $_identifier = null;
		
	/**
	 * 
	 * Register POSIX Signals
	 * @param $identifier
	 * @param $log
	 * @param $callback
	 * @param $signals
	 */
	public function __construct($identifier, $callback = null, $signals = null) {
		$this->_identifier = $identifier;
		if ($callback === null) {
			$callback = array(&$this, 'defaultHandler');
		}
		if ($signals === null) {
			$signals = array(SIGCHLD, SIGTERM, SIGHUP, SIGINT, SIGQUIT, SIGILL, SIGTRAP, SIGABRT, SIGBUS);
		}
		if ((is_array($signals)) && (count($signals)>0)) {
			foreach($signals as $signal) {
				pcntl_signal($signal, $callback);
			}
		}
	}


	/**
	 * 
	 * POSIX Signal handler callback
	 * @param $sig
	 */
	public function defaultHandler($sig) {
		switch ($sig) {
			case SIGTERM:
				// Shutdown
				\PhpTaskDaemon\Daemon\Logger::get()->log('Application (' . $this->_identifier . ') received SIGTERM signal (shutting down)', Zend_Log::DEBUG);
				exit;
			case SIGCHLD:
				// Halt
				\PhpTaskDaemon\Daemon\Logger::get()->log('Application (' . $this->_identifier . ') received SIGCHLD signal (halting)', Zend_Log::DEBUG);		
				while (pcntl_waitpid(-1, $status, WNOHANG) > 0);
				break;
			case SIGINT:
				// Shutdown
				\PhpTaskDaemon\Daemon\Logger::get()->log('Application (' . $this->_identifier . ') received SIGINT signal (shutting down)', Zend_Log::DEBUG);
				exit;
				break;
			default:
				\PhpTaskDaemon\Daemon\Logger::get()->log('Application (' . $this->_identifier . ') received ' . $sig . ' signal (unknown action)', Zend_Log::DEBUG);
				//exit;
				break;
		}
	}

}