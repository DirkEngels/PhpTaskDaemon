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
 * This class represents a manager which runs periodically based on a preset 
 * interval.  
 * 
 */
class Interval extends AbstractClass implements InterfaceClass {
	protected $_sleepTime = 5;
	
	/**
	 * Loads the queue and executes all tasks 
	 *
	 */
	public function executeManager() {
		while (true) {
			// Load Tasks in Queue
			$this->_queue = $this->_task->loadTasks();
			$this->_task->updateMemoryQueue(count($this->_queue), count($this->_queue));
	
			if (count($this->_queue)==0) {
				$this->_log->log("Queue checked: empty!!!", \Zend_Log::INFO);
			} else {
				$this->_log->log("Queue loaded: " . count($this->_queue) . " elements", \Zend_Log::INFO);
	
				while ($taskInput = array_shift($this->_queue)) {
					// Set manager input and start the manager
					$this->_task->setTaskInput($taskInput);
					$this->_task->updateMemoryTask(0);
					$this->_task->executeTask();
					$this->_task->updateMemoryTask(100);
					$this->_task->updateMemoryQueue(count($this->_queue));
					usleep(10);
					$this->_task->updateMemoryTask(0);
				}
			}
			
			// Take a small rest after so much work. This also prevents 
			// manageres from using all resources.
			$this->_sleep();
		}
	}
	
	/**
	 * 
	 * The sleep function for an interval manager
	 */
	protected function _sleep() {
		// Sleep
		$this->_log->log("Sleeping <interval> for : " . $this->_sleepTime . " micro seconds", \Zend_Log::INFO);
		sleep($this->_sleepTime);
	}

}
