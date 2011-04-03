<?php

namespace Tasks\Concept\PocTask;

use \PhpTaskDaemon\Queue as Queue;

class Queue extends Queue\AbstractClass implements Queue\InterfaceClass {

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

