<?php

namespace Tasks\Examples\Cron;
use \PhpTaskDaemon\Task\Job as PTDTJ;
use \PhpTaskDaemon\Task\Queue as PTDTQ;
use \PhpTaskDaemon\Task\Executor as PTDTE;
use \PhpTaskDaemon\Task\Queue\Statistics as PTDTQS;

// Manager
class Manager extends \PhpTaskDaemon\Task\Manager\Cron {
}

// Job
class Job extends PTDTJ\AbstractClass implements PTDTJ\InterfaceClass {
	protected $_inputFields = array('sleepTime');
}

// Queue
class Queue extends PTDTQ\AbstractClass implements PTDTQ\InterfaceClass {
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

// Manager
class Executor extends PTDTE\AbstractClass implements PTDTE\InterfaceClass {
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