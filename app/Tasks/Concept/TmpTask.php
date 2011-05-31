<?php

namespace Tasks\Concept\TmpTask;
use \PhpTaskDaemon\Task\Executor as PTDTE;
use \PhpTaskDaemon\Task\Job as PTDTJ;
use \PhpTaskDaemon\Task\Queue as PTDTQ;
use \PhpTaskDaemon\Task\Queue\Statistics as PTDTQS;

class Executor extends PTDTE\AbstractClass implements PTDTE\InterfaceClass {
	/**
	 * The proof of concept task executor file defines the algorithm to process
	 * a single job. In this case the job sleeps for a certain amount of time.  
	 */
	public function run() {
		$job = $this->getJob();
		$input = $job->getInput();

		// Sleep
		$sleepTimeProgress = round($job->getInputVar('sleepTime')/10);
		for ($i=1; $i<10; $i++) {
			usleep($sleepTimeProgress);
			$this->updateStatus(($i*10), 'Task data: ' . $job->getJobId());
		}

		// Return Status
		$returnStatus = (rand(0,1)==1) 
			? PTDTQS\BaseClass::STATUS_DONE 
			: PTDTQS\BaseClass::STATUS_FAILED;

		// Output
		$job->setOutput(array(
			'returnStatus' => $returnStatus,
			'waitTime' => rand(1,5)
		));
		
		return $job;
	}
}

class Job extends PTDTJ\AbstractClass implements PTDTJ\InterfaceClass {

	protected $_inputFields = array('sleepTime');

}

class Manager extends \PhpTaskDaemon\Task\Manager\Cron {
	protected $_sleepTime = 2;
}

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

