<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager;

/**
 * 
 * This class represents a manager which can have multiple instances running in
 * parallel.
 * 
 */
class Forked extends AbstractClass implements InterfaceClass {
	/**
	 * Runs the manager
	 * @see PhpTaskDaemon\Task\Manager.InterfaceClass::execute()
	 */
	public function execute() {
		while (true) {
			// Load Tasks in Queue
			$jobs = $this->getQueue()->load();

			if (count($jobs)==0) {
				$this->log("Queue checked: empty!!!", \Zend_Log::DEBUG);
				$this->getExecutor()->updateStatus(100, 'Queue empty');
			} else {
				$this->log("Queue loaded: " . count($jobs) . " elements", \Zend_Log::INFO);
				$this->getQueue()->updateQueue(count($jobs));
	
				$childs = 0;
				while ($job = array_shift($jobs)) {
					$this->_forkTask($job);
				}
				$this->log('Queue finished', \Zend_Log::DEBUG);
				$this->getExecutor()->updateStatus(100, 'Queue finished');

				// The manager waits
				while ($childs>=3) {
					pcntl_wait($status);
					$childs--;	
				}
				
			}
			
			// Take a small rest after so much work. This also prevents 
			// manageres from using all resources.
			$this->_sleep();
		}
	}	
	
	protected function _forkTask($job) {
		// Fork the manager
		$pid = pcntl_fork();
		
		if ($pid == -1) {
			die ('Could not fork.. dunno why not... shutting down... bleep bleep.. blap...');
		} elseif ($pid) {
			// The manager waits later
			$childs++;
		} else {
			// Set manager input and start the manager
			$this->_processTask($job);
			
			// Exit after finishing the forked
			exit;
		}
	} 
}
