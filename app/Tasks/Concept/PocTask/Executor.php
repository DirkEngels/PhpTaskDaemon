<?php

namespace Tasks\Concept\PocTask;
use \PhpTaskDaemon\Task\Executor as PTDTE;

class Executor extends PTDTE\AbstractClass implements PTDTE\InterfaceClass {
	const RETURN_STATE_DONE = 'Done';
	const RETURN_STATE_FAILED = 'Failed';
	
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
			$this->updateStatus(($i*10), 'Task data: ' . $job->getJobId());
		}
		$this->updateStatus(100, 'Task finished');

		// Output
		$returnStatus = (rand(0,1)==1) 
			? self::RETURN_STATE_DONE 
			: self::RETURN_STATE_FAILED; 
		$job->setOutput(array(
			'returnStatus' => $returnStatus,
			'waitTime' => rand(1,5)
		));
		
		return $job;
	}
}
