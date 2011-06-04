<?php

namespace Tasks\Concept\PocTask;
use \PhpTaskDaemon\Task\Queue as PtdQ;

class Queue extends PtdQ\AbstractClass implements PtdQ\InterfaceClass {

	/**
	 * Fills a queue with a random number of tasks (0 - 30). The input for each
	 * task will be a single variable containing the amount of miliseconds to
	 * sleep.
	 */
	public function load() {
		$queue = array();
		for ($i=0; $i<rand(0,100); $i++) {
			$job = new Job(
				'pocjob-' . $i, 
				array('sleepTime' => rand(100000, 5000000))
			);
			array_push($queue, $job);
		}
		return $queue;
	}
	
}

