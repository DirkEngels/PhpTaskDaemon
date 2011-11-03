<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager\Process
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager\Process;

abstract class AbstractClass {

    const SLEEPTIME = 100;
    protected $_queue = null;
    protected $_executor = null;
    protected $_jobs = array();


    /**
     * 
     * Returns the current loaded queue array
     * @return \PhpTaskDaemon\Task\Queue\AbstractClass
     */
    public function getQueue() {
        return $this->_queue;
    }


    /**
     * 
     * Sets the current queue to process.
     * @param \PhpTaskDaemon\Task\Queue\AbstractClass $queue
     * @return $this
     */
    public function setQueue($queue) {
        if (!($queue instanceof \PhpTaskDaemon\Task\Queue\AbstractClass)) {
            $queue = new \PhpTaskDaemon\Task\Queue\DefaultClass();
        }
        $this->_queue = $queue;

        return $this;
    }


    /**
     * 
     * Returns the executor object
     * @return \PhpTaskDaemon\Task\Executor\AbstractClass
     */
    public function getExecutor() {
        if (!is_a($this->_executor, '\PhpTaskDaemon\Task\Executor\AbstractClass')) {
            $this->_executor = new \PhpTaskDaemon\Task\Executor\DefaultClass();
        }
        return $this->_executor;
    }


    /**
     * 
     * Sets the current executor object.
     * @param \PhpTaskDaemon\Task\Executor\AbstractClass $executor
     * @return $this
     */
    public function setExecutor($executor) {
        if (is_a($executor, '\PhpTaskDaemon\Task\Executor\AbstractClass')) {
            $this->_executor = $executor;
        }

        return $this;
    }


    /**
     * Gets the job
     * @return array
     */
    public function getJobs() {
        return $this->_jobs;
    }


    /**
     * Sets the jobs
     * @param array $jobs
     */
    public function setJobs($jobs) {
        $this->_jobs = $jobs;
    }


    /**
     * 
     * Process a single task: set job input, reset status, run and update
     * statistics
     * @param \PhpTaskDaemon\Task\Job\AbstractClass $job
     */
    protected function _processTask(\PhpTaskDaemon\Task\Job\AbstractClass $job) {
        // Set manager input
         \PhpTaskDaemon\Daemon\Logger::get()->log(getmypid() . ": Started: " . $job->getJobId(), \Zend_Log::DEBUG);
        $executor = $this->getExecutor();
        $executor->setJob($job);
        $queue = $this->getQueue();

        // Update Status before and after running the task
        $executor->updateStatus(0);
        $job = $executor->run();
        $executor->updateStatus(100);

        // Log and sleep for a while
        usleep(self::SLEEPTIME);
        \PhpTaskDaemon\Daemon\Logger::get()->log(getmypid() . ': ' . $job->getOutput()->getVar('returnStatus') . ": " . $job->getJobId(), \Zend_Log::DEBUG);            

        // Reset status and decrement queue
        $executor->updateStatus(0);
        $queue->updateStatistics($job->getOutput()->getVar('returnStatus'));
        $queue->updateQueue();

        return $job->getOutput()->getVar('returnStatus');
    }


    /**
     * Forks a single tasks.
     * @param unknown_type $job
     */
    protected function _forkTask($job) {
        // Fork the manager
        $pid = pcntl_fork();

        if ($pid == -1) {
            die ('Could not fork.. dunno why not... shutting down... bleep bleep.. blap...');
        } elseif ($pid) {
            // The manager waits later
        } else {
            // Initiate resources
            // @todo: Initiate resources

            // Set manager input and start the manager
            $this->_processTask($job);

            // Cleanup resources
            // @todo: Cleanup resources

            // Exit after finishing the forked
            exit;
        }
    } 

}