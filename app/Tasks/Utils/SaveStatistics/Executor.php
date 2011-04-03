<?php

namespace Utils\SaveStatistics;

class Executor extends \PhpTaskDaemon\Executor\AbstractClass implements \PhpTaskDaemon\Executor\InterfaceClass {
	
	public function run() {
		$input = $this->getJob()->getInput();
		
		// Sleep
		for ($i=1; $i<10; $i++) {
			usleep($this->getJob()->getInputVar('sleepTime'));
			$this->updateMemoryTask(($i*10), 'Task data: ' . $this->getJob()->getJobId());
		}

		// Output
		$status = (rand(0,1)==1) ? 'Done' : 'Failed'; 
		$this->getJob()->setOutput(array(
			'status' => $status,
			'waitTime' => rand(1,5)
		));
		
		return $this->getJob();
	}
}

