<?php

namespace Tasks\Examples\Minimal;

use \PhpTaskDaemon\Task\Executor as PTDTE;

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