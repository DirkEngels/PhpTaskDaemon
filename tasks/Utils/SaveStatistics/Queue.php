<?php

namespace Utils\SaveStatistics;

class Queue extends \PhpTaskDaemon\Queue\AbstractClass implements \PhpTaskDaemon\Queue\InterfaceClass {

	/**
	 * Fills a queue with a random number of tasks (0 - 30). The input for each
	 * task will be a single variable containing the amount of miliseconds to
	 * sleep.
	 * @see PhpTaskDaemon\Queue.InterfaceClass::load()
	 */
	public function load() {
		$queue = array();
		if (count($queue)==0) {
			for ($i=0; $i<rand(0,30); $i++) {
				$job = new Job(
					'jobId: ' . $i, 
					array('sleepTime' => rand(100000, 500000))
				);
				array_push($queue, $job);
			}
		}
		return $queue;
	}
	
}

