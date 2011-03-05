<?php
/**
 * @package SiteSpeed
 * @subpackage Daemon\Task
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace SiteSpeed\Daemon\Task;
use \SiteSpeed\Daemon\Manager as Manager;

/**
 * 
 * This example class shows a task running with an interval manager. Every task
 * can adjust the time to wait after finishing all tasks and running again. 
 * This example is called sloppy, because the time between loading a queue and
 * executing its tasks is at a random interval. 
 *
 */
class Example5 extends AbstractClass implements InterfaceClass {

	static protected $_managerType = Manager\AbstractClass::PROCESS_TYPE_FORKED;
	
	/** 
	 * Task input variables: These items must be present in the task
	 * input data in order to execute the task.
	 */
	protected $_inputFields = array('taskId', 'sleepTime');
	
	/**
	 * Loads the task queue: The input for one or more tasks. The input of a 
	 * sleep task must contain the following items:	
	 * - taskId
	 * - sleepTime
	 * 
	 * @see Dew_Daemon_Task::loadTaskQueue()
	 */
	public function loadTasks() {
		$queue = array();
		if (count($queue)==0) {
			for ($i=0; $i<rand(0,30000); $i++) {
				array_push($queue, array('taskId' => $i, 'sleepTime' => rand(1000, 5000)));
			}
			// Inform the manager about the amount of tasks loaded into the queue
			$this->updateMemoryQueue(count($queue));
		}

		return $queue;
	}
	
	/**
	 * Execute the task: Sleeps for a little while.
	 * 
	 * @see Dew_Daemon_Task::executeTask()
	 */
	public function executeTask() {
		$inputData = $this->getTaskInput();
		
		// Sleep
		$data = substr(md5(uniqid()), 0,10);
		for ($i=1; $i<10; $i++) {
			usleep($inputData['sleepTime']);
			$this->updateMemoryTask(($i*10), 'Task data: ' . $data);
		}

		// Override sleeping time
		$this->_sleepTime = rand(1,5);
		return $this->_sleepTime;
	}
	
}