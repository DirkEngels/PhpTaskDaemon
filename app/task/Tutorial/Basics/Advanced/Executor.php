<?php

namespace PhpTaskDaemon\Task\Tutorial\Basics\Advanced;

use \PhpTaskDaemon\Task\Executor as TaskExecutor;
use \PhpTaskDaemon\Task\Queue as TaskQueue;

require_once(__DIR__ . '/Queue.php');

class Executor extends TaskExecutor\ExecutorAbstract implements TaskExecutor\ExecutorInterface {
    public function run() {
        $job = $this->getJob();

        // Sleep
        $sleepTimeProgress = round($job->getInput()->getVar('sleepTime')/10);
        for ($i=1; $i<10; $i++) {
            usleep($sleepTimeProgress);
            $this->setStatus(($i*10), 'Task data: ' . $job->getJobId());
        }

        // Return Status
        $returnStatus = (rand(0,1)==1) 
            ? TaskQueue\QueueAbstract::STATUS_DONE 
            : TaskQueue\QueueAbstract::STATUS_FAILED;

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
