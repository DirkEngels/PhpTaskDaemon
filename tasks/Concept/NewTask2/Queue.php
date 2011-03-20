<?php
/**
 * @package App
 * @subpackage Concept\NewTask2
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace App\Concept\NewTask2;
use \PhpTaskDaemon\Queue as Queue;

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
