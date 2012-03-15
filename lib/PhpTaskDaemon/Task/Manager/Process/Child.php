
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
     * Forks the task to a seperate process.
     */
    public function run() {

        $jobs = $this->getJobs();
        foreach($jobs as $job) {
            $pid = pcntl_fork();
            $this->_childCount++;

            if ($pid == -1) {
                die ('Could not fork.. dunno why not... shutting down... bleep bleep.. blap...');
            } elseif ($pid) {
                $this->getQueue()->resetIpc();
                $this->getQueue()->getIpc()->addArrayVar('executors', $pid);

                // Continue parent process
                $this->runParent($pid);
            } else {
                // Run child process
                $this->runChild($job);
            }
        }

        \PhpTaskDaemon\Daemon\Logger::log('Finished current set of tasks!', \Zend_Log::NOTICE);
    }


    /**
     * Handles executing the parent process after forking.
     * 
     * @param unknown_type $pid
     */
    public function runParent($pid) {
        // The manager waits later
        \PhpTaskDaemon\Daemon\Logger::log('Spawning child process: ' . $pid . '!', \Zend_Log::NOTICE);

        try {
            $this->getQueue()->resetIpc();
            $this->getQueue()->getIpc()->addArrayVar('executors', $pid);
            $pid = pcntl_wait($status);

            $this->_childCount--;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


    /**
     * Handles executing the child process after forking.
     * 
     * @param \PhpTaskDaemon\Task\job\Data\DataInterface $job
     */
    public function runChild($job) {
        \PhpTaskDaemon\Daemon\Logger::log('Processing task started!', \Zend_Log::NOTICE);
        $this->getExecutor()->resetPid();
        $this->getExecutor()->resetIpc();
        $this->getQueue()->resetIpc();

        try {
            $this->_processTask($job);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        \PhpTaskDaemon\Daemon\Logger::log('Processing task done!', \Zend_Log::NOTICE);

        // Clean up IPC
        $this->getQueue()->getIpc()->removeArrayVar('executors', getmypid());
        $this->getExecutor()->resetIpc();
        $this->getExecutor()->getIpc()->remove();

        exit(1);
    }

}