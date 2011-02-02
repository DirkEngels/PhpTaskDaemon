<?php

/**
 * @package SiteSpeed
 * @subpackage Daemon\Manager
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace SiteSpeed\Daemon\Manager;

/**
 * 
 * This class represents a manager which can have multiple instances running in
 * parallel.
 * 
 */
class Forked extends AbstractClass implements InterfaceClass {
	
	protected $_sleepTime = 3;
	
	public function executeManager() {
		while (true) {
			// Load Tasks
			$this->_queue = $this->_task->loadTasks();
			$this->updateMemoryQueue(count($this->_queue));
			
			if (count($this->_queue)==0) {
				$this->_log->log("Queue checked: empty!!!", Zend_Log::INFO);
				
			} else {
				$this->_log->log("Queue loaded: " . count($this->_queue) . " items!!!", Zend_Log::INFO);
	
				$childs = 0;
				while ($taskInput = array_shift($this->_queue)) {
					$this->_forkTask($taskInput);
				}
	
				// The manager waits
				while ($childs>=3) {
					pcntl_wait($status);
					$childs--;	
				}
			}
	
			$this->_log->log('Current queue finished... taking a small sleep ' . $this->_sleepTime, Zend_Log::INFO);
			sleep($this->_sleepTime);
		}
	}	
	
	protected function _forkTask($taskInput) {
		// Fork the manager
		$pid = pcntl_fork();
		
		if ($pid == -1) {
			die ('Could not fork.. dunno why not... shutting down... bleep bleep.. blap...');
		} elseif ($pid) {
			// The manager waits later
			$childs++;
		} else {
			// Set manager input and start the manager
			$this->_task->setTaskInput($taskInput);
			$this->_task->executeTask();
			
			// Exit after finishing the forked
			exit;
		}
	} 
}
