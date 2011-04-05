<?php

namespace Tasks\Concept\CopyTask;
use \PhpTaskDaemon\Task\Queue as PTDTQ;

class Queue extends PTDTQ\AbstractClass implements PTDTQ\InterfaceClass {

	/**
	 * Fills a queue with a random number of tasks (0 - 30). The input for each
	 * task will be a single variable containing the amount of miliseconds to
	 * sleep.
	 */
	public function load() {
		$queue = array();
		for ($i=0; $i<rand(500,5000); $i++) {
			$job = new Job(
				'copyjob-' . $i, 
				array('sleepTime' => rand(10000, 50000))
			);
			array_push($queue, $job);
		}
		return $queue;
	}
	
}

