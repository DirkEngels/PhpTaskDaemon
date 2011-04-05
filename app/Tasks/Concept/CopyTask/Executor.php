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
		$this->updateStatus(0, 'Initializing task');
		for ($i=1; $i<10; $i++) {
			usleep($sleepTimeProgress);
			echo ".";
			$this->updateStatus(($i*10), 'Task data: ' . $job->getJobId());
		}
		echo "\n";
		$this->updateStatus(100, 'Task finished');

		// Output
		$returnStatus = (rand(0,1)==1) 
			? PTDTQS\BaseClass::STATUS_DONE 
			: PTDTQS\BaseClass::STATUS_FAILED; 
		$job->setOutput(array(
			'returnStatus' => $returnStatus,
			'waitTime' => rand(1,5)
		));
		
		return $job;
	}
}
