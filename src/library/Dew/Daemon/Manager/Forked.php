<?php

/**
 * 
 * This class represents a manager which can have multiple instances running in
 * parallel.
 * 
 * @author DirkEngels 
 * 
 */
class Dew_Daemon_Manager_Forked extends Dew_Daemon_Manager_Abstract implements Dew_Daemon_Manager_Interface {
	
	protected $_sleepTime = 3;
	
	public function executeManager() {
		while (true) {
			// Load Tasks
			$this->_queue = $this->_task->loadTasks();
			
			if (count($this->_queue)==0) {
//				echo "Queue checked: empty!!!\n";
				
			} else {
				echo "Queue loaded: " . count($this->_queue) . " items!!!\n";

				$childs = 0;
				while ($task = array_shift($this->_queue)) {
					// Fork the manager
					$pid = pcntl_fork();
					
					if ($pid == -1) {
						die ('Could not fork.. dunno why not... shutting down... bleep bleep.. blap...');
					} elseif ($pid) {
						// The manager waits later
						$childs++;
					} else {
						// Set manager input and start the manager
						$this->_task->setTaskInput($task);
						$this->_task->executeTask();
						
						// Exit after finishing the forked
						exit;
					}
				}

				// The manager waits
				while ($childs>=3) {
					pcntl_wait($status);
					$childs--;	
				}
			}

			$this->_log('Current queue finished... taking a small ' . $this->_sleepTime);
			sleep($this->_sleepTime);
		}
	}	
}
