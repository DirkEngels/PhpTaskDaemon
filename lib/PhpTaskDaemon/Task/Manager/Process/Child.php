<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager\Process
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager\Process;
use PhpTaskDaemon\Daemon\Logger;

class Child extends ProcessAbstract implements ProcessInterface {

    /**
     * Forks the task to a seperate process
     */
    public function run() {

        $jobs = $this->getJobs();
        foreach($jobs as $job) {
            $pid = pcntl_fork();

            if ($pid == -1) {
                die ('Could not fork.. dunno why not... shutting down... bleep bleep.. blap...');
            } elseif ($pid) {
                // Continue parent process
                $this->runParent($pid);
            } else {
                // Run child process
                $this->runChild();
            }
        }

        \PhpTaskDaemon\Daemon\Logger::log('Finished current set of tasks!', \Zend_Log::NOTICE);
    }

    public function runParent($pid) {
        // The manager waits later
        \PhpTaskDaemon\Daemon\Logger::log('Processing manager starting!', \Zend_Log::NOTICE);

        try {
            $res = pcntl_waitpid($pid, $status);
            \PhpTaskDaemon\Daemon\Logger::log('Processing manager done!', \Zend_Log::NOTICE);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


    public function runChild() {
        $this->getExecutor()->getStatus()->resetPid();
        $this->getExecutor()->getStatus()->resetIpc();
        $this->getQueue()->getStatistics()->resetIpc();

        \PhpTaskDaemon\Daemon\Logger::log('Processing task started!', \Zend_Log::NOTICE);
        try {
            $this->_processTask($job);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        \PhpTaskDaemon\Daemon\Logger::log('Processing task done!', \Zend_Log::NOTICE);
        exit(1);
    }

}