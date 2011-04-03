<?php

/**
 * @package SiteSpeed
 * @subpackage Daemon\Manager
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager;

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
	public function execute() {
		while (true) {			
			// Load Tasks in Queue
			$jobs = $this->getQueue()->load();
			$executor = $this->getExecutor();

			if (count($jobs)==0) {
				$this->log("Queue checked: empty!!!", \Zend_Log::INFO);
			} else {
				$this->log("Queue loaded: " . count($jobs) . " elements", \Zend_Log::INFO);
	
				while ($job = array_shift($jobs)) {
					// Set manager input and start the manager
 					$this->log("Job started: " . $job->getJobId(), \Zend_Log::DEBUG);
					$executor->setJob($job);
					$executor->updateStatus(0);
					$output = $executor->run(); 
					$executor->updateStatus(100);

					$this->log("Job done: " . $job->getJobId(), \Zend_Log::DEBUG);
					
					usleep(10);
					$this->getQueue()->updateStatistics(
						$job->getOutputVar('returnStatus')
					);
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
