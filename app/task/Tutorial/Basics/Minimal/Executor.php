<?php

namespace PhpTaskDaemon\Task\Tutorial\Basics\Minimal;

use \PhpTaskDaemon\Task\Executor as TaskExecutor;
use \PhpTaskDaemon\Task\Queue\Statistics;

class Executor extends TaskExecutor\AbstractClass implements TaskExecutor\InterfaceClass {

    public function run() {
        $job = $this->getJob();
        $input = $job->getInput();
        $output = $job->getOutput();

        // Input
        $sleepTime = (int) $job->getInput()->getVar('sleepTime');

        // Sleep
        $sleepTimeProgress = round($sleepTime);
        for ($i=1; $i<10; $i++) {
            usleep($sleepTimeProgress);
            $this->updateStatus(($i*10), 'Task data: ' . $job->getJobId());
        }

        // Output (status)
        $job->getOutput()->set(array(
            'returnStatus' => Statistics\DefaultClass::STATUS_DONE,
        ));

        return $job;
    }

}
