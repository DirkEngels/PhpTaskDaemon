<?php
/**
 * @package App
 * @subpackage Concept\NewTask2
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace App\Concept\NewTask2;
use \PhpTaskDaemon\Executor as Executor;

class Executor extends Excutor\AbstractClass implements Executor\InterfaceClass {
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
