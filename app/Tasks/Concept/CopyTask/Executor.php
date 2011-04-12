<?php

namespace Tasks\Concept\CopyTask;
use \PhpTaskDaemon\Task\Executor as PTDTE;
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
