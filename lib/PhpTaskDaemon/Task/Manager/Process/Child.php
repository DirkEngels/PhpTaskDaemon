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

    protected $_childCount = 0;

    /**
     * Forks the task to a seperate process
     */
    public function run() {

        $jobs = $this->getJobs();
        foreach($jobs as $job) {
            $pid = pcntl_fork();
            $this->_childCount++;

            if ($pid == -1) {
                die ('Could not fork.. dunno why not... shutting down... bleep bleep.. blap...');
            } elseif ($pid) {
                $this->getQueue()->getStatistics()->resetIpc();
                $this->getQueue()->getStatistics()->getIpc()->addArrayVar('executors', $pid);

                // Continue parent process
                $this->runParent($pid);
            } else {
                // Run child process
                $this->runChild($job);
            }
        }

        \PhpTaskDaemon\Daemon\Logger::log('Finished current set of tasks!', \Zend_Log::NOTICE);
    }

    public function runParent($pid) {
        // The manager waits later
        \PhpTaskDaemon\Daemon\Logger::log('Spawning child process: ' . $pid . '!', \Zend_Log::NOTICE);

        try {
            $this->getQueue()->getStatistics()->resetIpc();
            $this->getQueue()->getStatistics()->getIpc()->addArrayVar('executors', $pid);
            $pid = pcntl_wait($status);

            $this->_childCount--;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


    public function runChild($job) {
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

        // Clean up IPC
        $this->getQueue()->getStatistics()->getIpc()->removeArrayVar('executors', getmypid());
        $this->getExecutor()->getStatus()->resetIpc();
        $this->getExecutor()->getStatus()->getIpc()->remove();

        exit(1);
    }

}