<?php

namespace PhpTaskDaemon\Task\Example\Minimal;

use \PhpTaskDaemon\Task\Executor as TaskExecutor;
use \PhpTaskDaemon\Task\Queue\Statistics;

class Executor extends TaskExecutor\AbstractClass implements TaskExecutor\InterfaceClass {

    public function run() {
        $job = $this->getJob();

        // Sleep
        $sleepTimeProgress = round($job->getInput()->getVar('sleepTime')/10);
        for ($i=1; $i<10; $i++) {
            usleep($sleepTimeProgress);
            $this->updateStatus(($i*10), 'Task data: ' . $job->getJobId());
        }

        // Return Status
        $returnStatus = (rand(0,1)==1) 
            ? Statistics\DefaultClass::STATUS_DONE 
            : Statistics\DefaultClass::STATUS_FAILED;

        // Output
        $job->getOutput()->set(
            array(
                'returnStatus' => $returnStatus,
                'waitTime' => rand(1,5)
            )
        );

        return $job;
    }

}
