<?php

/**
 * @package SiteSpeed
 * @subpackage Daemon\Manager
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Manager;

/**
 * 
 * This class represents a manager which runs periodically based on a preset 
 * interval.  
 * 
 */

use PocTask\Job;

class Interval extends AbstractClass implements InterfaceClass {
	protected $_sleepTime = 5;
	
	/**
	 * Loads the queue and executes all tasks 
	 *
	 */
	public function execute() {
		while (true) {			
			// Load Tasks in Queue
			$jobs = $this->getQueue()->load();
	
			if (count($jobs)==0) {
				$this->log("Queue checked: empty!!!", \Zend_Log::INFO);
			} else {
				$this->log("Queue loaded: " . count($this->getQueue()) . " elements", \Zend_Log::INFO);
	
				while ($job = array_shift($jobs)) {
					// Set manager input and start the manager
					$executor = new \PhpTaskDaemon\Executor\BaseClass($job);
					$executor->updateStatus(0);
					$retVal = $executor->run(); 
					$executor->updateStatus(100);

					usleep(10);
					$status = ($retVal==1) ? 'Done' : 'Failed';
					$this->getQueue()->updateStatistics($status);
					$executor->updateStatus(0);
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
		$this->log("Sleeping <interval> for : " . $this->_sleepTime . " micro seconds", \Zend_Log::INFO);
		sleep($this->_sleepTime);
	}

}
