<?php

/**
 * 
 * This class represents a manager which runs periodically based on a preset 
 * interval.  
 * 
 * 
 * @author DirkEngels <d.engels@dirkengels.com> 
 * 
 */
class Dew_Daemon_Manager_Interval extends Dew_Daemon_Manager_Abstract implements Dew_Daemon_Manager_Interface {

	protected $_sleepTime = 5;
	
	
	/**
	 * Loads the queue and executes all tasks 
	 *
	 */
	public function executeManager() {
		while (true) {
			// Load Tasks in Queue
			$this->_queue = $this->_task->loadTasks();

			if (count($this->_queue)==0) {
				echo "Queue checked: empty!!!\n";
			} else {
				echo "Queue loaded: " . count($this->_queue) . " elements\n";
	
				while ($taskInput = array_shift($this->_queue)) {
					// Set manager input and start the manager
					$this->_task->setTaskInput($taskInput);
					$this->_task->updateMemoryTask(0);
					$this->_task->executeTask();
					$this->_task->updateMemoryTask(100);
					usleep(10);
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
		echo "Sleeping <interval> for : " . $this->_sleepTime . " micro seconds\n";
		sleep($this->_sleepTime);
	}

}
