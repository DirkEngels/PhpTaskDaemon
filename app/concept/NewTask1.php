<?php
/**
 * @package App
 * @subpackage Concept\NewTask1
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace App\Concept\NewTask1;
use \PhpTaskDaemon\Queue as Queue;
use \PhpTaskDaemon\Executor as Executor;

/**
 * 
 * The NewTask1 Queue loads the queue for each time a task is checked for
 * execution.
 *
 */
class Queue extends Queue\AbstractClass implements Queue\InterfaceClass {
	/**
	 * Loads the task queue: The input for one or more tasks. The input of a 
	 * sleep task must contain the following items:	
	 * - taskId
	 * - sleepTime
	 * 
	 * @see Task::loadTaskQueue()
	 */
	public function load() {
		$queue = array();
		if (count($queue)==0) {
			for ($i=0; $i<rand(0,30); $i++) {
				array_push(
					$queue, 
					array('taskId' => $i, 'sleepTime' => rand(100000, 500000))
				);
			}
		}
	
		return $queue;
	}

}

/**
 * 
 * The NewTask1 Executor actually executes a single task
 *
 */
class Executor extends Task\AbstractClass implements Task\InterfaceClass {
	/**
	 * Execute the task: Sleeps for a little while.
	 * 
	 * @see Task::executeTask()
	 */
	public function run() {
		$inputData = $this->getTaskInput();
		
		// Sleep
		$randomString = substr(md5(uniqid()), 0,10);
		for ($i=1; $i<10; $i++) {
			usleep($inputData['sleepTime']);
			$this->updateMemoryTask(($i*10), 'Task data: ' . $randomString);
		}

		// Override sleeping time
		$this->_sleepTime = rand(1,5);
		return $this->_sleepTime;
	}
}
