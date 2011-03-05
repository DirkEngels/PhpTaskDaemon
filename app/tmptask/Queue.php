<?php
/**
 * @package SiteSpeed
 * @subpackage Daemon\Task
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace App\Tmp;
use \PhpTaskDaemon\Queue as Queue;

class Queue extends Queue\AbstractClass implements Queue\InterfaceClass {

	static protected $_managerType = Manager\AbstractClass::PROCESS_TYPE_INTERVAL;
	
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
