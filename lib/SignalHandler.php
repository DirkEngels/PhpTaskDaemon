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
* The SiteSpeed Daemon Signals object can be encapsulated by other classes to 
* enable posix signal handlers. These are needed for triggering the cleaning up
* the daemon, managers and possible active tasks when receiving a posix signal.
* A callback can be provided to run a method related to the current process. 
* 
*/
class SignalHandler {

	/**
	 * Task identifier
	 * @var string|null 
	 */
	protected $_identifier = null;
	
	/**
	 * 
	 * Zend_Log object
	 * @var Zend_Log|null
	 */
	protected $_log = null;
	
	/**
	 * 
	 * Register POSIX Signals
	 * @param $sig
	 */
	public function __construct($identifier, $log = null, $callback = null, $signals = null) {
		$this->_identifier = $identifier;
		$this->_log = $log;
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
	 * System log 
	 * @param string $message
	 */
	public function log($message, $level = Zend_Log::INFO) {
		if (is_object($this->_log)) {
			$this->_log->log($message, $level);
		} else {
			echo "SYS: (" . $level . ") " .  $message . "\n";
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
				$this->log('Application (' . $this->_identifier . ') received SIGTERM signal (shutting down)', Zend_Log::DEBUG);
				exit;
			case SIGCHLD:
				// Halt
				$this->log('Application (' . $this->_identifier . ') received SIGCHLD signal (halting)', Zend_Log::DEBUG);		
				while (pcntl_waitpid(-1, $status, WNOHANG) > 0);
				break;
			case SIGINT:
				// Shutdown
				$this->log('Application (' . $this->_identifier . ') received SIGINT signal (shutting down)', Zend_Log::DEBUG);
				exit;
				break;
			default:
				$this->log('Application (' . $this->_identifier . ') received ' . $sig . ' signal (unknown action)', Zend_Log::DEBUG);
				//exit;
				break;
		}
	}

}