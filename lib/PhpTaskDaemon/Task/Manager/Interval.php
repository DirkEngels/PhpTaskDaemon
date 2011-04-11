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
 * This class represents a manager which runs periodically based on a preset 
 * interval.  
 */
class Interval extends AbstractClass implements InterfaceClass {
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
	
				while ($job = array_shift($jobs)) {
					$this->_processTask($job);
				}
				$this->log('Queue finished', \Zend_Log::DEBUG);
				$this->getExecutor()->updateStatus(100, 'Queue finished');
			}
			
			// Take a small rest after so much work. This also prevents 
			// manageres from using all resources.
			$this->_sleep();
		}
	}
}
