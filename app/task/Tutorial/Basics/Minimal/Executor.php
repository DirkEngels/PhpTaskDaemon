<?php

namespace PhpTaskDaemon\Task\Tutorial\Basics\Minimal;

use \PhpTaskDaemon\Task\Executor as TaskExecutor;
use \PhpTaskDaemon\Task\Queue as TaskQueue;
use \PhpTaskDaemon\Daemon\Logger;

class Executor extends TaskExecutor\ExecutorAbstract implements TaskExecutor\ExecutorInterface {

    public function run() {
        $job = $this->getJob();
        $input = $job->getInput();
        $output = $job->getOutput();

        // Input
        $sleepTime = (int) $job->getInput()->getVar('sleepTime');

        Logger::log('Sleeping for ' . $sleepTime . ' milliseconds', \Zend_Log::NOTICE);

        // Sleep
        $sleepTimeProgress = round($sleepTime);
        for ($i=1; $i<10; $i++) {
            usleep($sleepTimeProgress);
            $this->setStatus(($i*10), 'Task data: ' . $job->getJobId());
        }

        // Output (status)
        $job->getOutput()->set(array(
            'returnStatus' => TaskQueue\QueueAbstract::STATUS_DONE,
        ));

        return $job;
    }

}
