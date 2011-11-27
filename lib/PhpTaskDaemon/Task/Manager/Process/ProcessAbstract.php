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

abstract class ProcessAbstract {

    const SLEEPTIME = 100;
    protected $_name = NULL;
    protected $_queue = NULL;
    protected $_executor = NULL;
    protected $_jobs = array();


    public function __construct($name = NULL) {
        $this->_name = $name;
    }


    /**
     *
     * Returns the task name 
     * @return string
     */
    public function getName() {
        return $this->_name;
    }


    /**
     *
     * Sets the task name 
     * @param string $name
     * @return $this
     */
    public function setName($name) {
        $this->_name = $name;
        return $this;
    }


    /**
     * 
     * Returns the current loaded queue array
     * @return \PhpTaskDaemon\Task\Queue\QueueAbstract
     */
    public function getQueue() {
        if (!($this->_queue instanceof \PhpTaskDaemon\Task\Queue\QueueAbstract)) {
            $this->_queue = new \PhpTaskDaemon\Task\Queue\QueueDefault();
        }
        return $this->_queue;
    }


    /**
     * 
     * Sets the current queue to process.
     * @param \PhpTaskDaemon\Task\Queue\QueueAbstract $queue
     * @return $this
     */
    public function setQueue($queue) {
        if (!($queue instanceof \PhpTaskDaemon\Task\Queue\QueueAbstract)) {
            throw new \Exception('Invalid Queue object');
        }
        $this->_queue = $queue;

        return $this;
    }


    /**
     * 
     * Returns the executor object
     * @return \PhpTaskDaemon\Task\Executor\ExecutorAbstract
     */
    public function getExecutor() {
        if (!is_a($this->_executor, '\PhpTaskDaemon\Task\Executor\ExecutorAbstract')) {
            $this->_executor = new \PhpTaskDaemon\Task\Executor\ExecutorDefault();
        }
        return $this->_executor;
    }


    /**
     * 
     * Sets the current executor object.
     * @param \PhpTaskDaemon\Task\Executor\ExecutorAbstract $executor
     * @return $this
     */
    public function setExecutor($executor) {
        if (is_a($executor, '\PhpTaskDaemon\Task\Executor\ExecutorAbstract')) {
            $this->_executor = $executor;
        }

        return $this;
    }


    /**
     * Gets the job
     * @return array[\PhpTaskDaemon\Task\Job\JobAbstract]
     */
    public function getJobs() {
        return $this->_jobs;
    }


    /**
     * Sets the jobs
     * @param array[\PhpTaskDaemon\Task\Job\JobAbstract] $jobs
     * @return $this
     */
    public function setJobs($jobs) {
        $this->_jobs = $jobs;
        return $this;
    }


    /**
     * 
     * Process a single task: set job input, reset status, run and update
     * statistics
     * @param \PhpTaskDaemon\Task\Job\JobAbstract $job
     */
    protected function _processTask(\PhpTaskDaemon\Task\Job\JobAbstract $job) {
        // Set manager input
        \PhpTaskDaemon\Daemon\Logger::log(getmypid() . ": Started: " . $job->getJobId(), \Zend_Log::DEBUG);
        $executor = $this->getExecutor();
        $executor->setJob($job);
        $queue = $this->getQueue();

        $executor->getStatus()->resetIpc();
        $executor->getStatus()->resetPid();
        $queue->getStatistics()->resetIpc();

        // Update Status before and after running the task
        $executor->updateStatus(0);
        $job = $executor->run();
        $executor->updateStatus(100);

        // Log and sleep for a while
        usleep(self::SLEEPTIME);
        \PhpTaskDaemon\Daemon\Logger::log(getmypid() . ': ' . $job->getOutput()->getVar('returnStatus') . ": " . $job->getJobId(), \Zend_Log::DEBUG);            

        // Reset status and decrement queue
        $queue->updateStatistics($job->getOutput()->getVar('returnStatus'));
        $queue->updateQueue();

        return $job->getOutput()->getVar('returnStatus');
    }


    /**
     * Forks a single tasks.
     * @param \PhpTaskDaemon\Task\Job\JobAbstract $job
     */
    protected function _forkTask($job) {
        // Fork the manager
        $pid = pcntl_fork();

        if ($pid == -1) {
            die ('Could not fork.. dunno why not... shutting down... bleep bleep.. blap...');
        } elseif ($pid) {
            // The manager waits later
            return $pid;

        } else {
            // @todo: Initiate resources
            $this->getExecutor()->getStatus()->getIpc()->initResource();

            // Set manager input and start the manager
            $this->_processTask($job);

            // Cleanup resources
            $this->getExecutor()->getStatus()->getIpc()->cleanupResource();

            // Exit after finishing the forked
            exit;
        }
    } 


    /**
     * 
     * POSIX Signal handler callback
     * @param $sig
     */
    public function sigHandler($sig) {
        switch ($sig) {
            case SIGTERM:
                // Shutdown
                \PhpTaskDaemon\Daemon\Logger::log('Application (PROCESS) received SIGTERM signal (shutting down)', \Zend_Log::DEBUG);
                break;
            case SIGCHLD:
                // Halt
                \PhpTaskDaemon\Daemon\Logger::log('Application (PROCESS) received SIGCHLD signal (halting)', \Zend_Log::DEBUG);
                while (pcntl_waitpid(-1, $status, WNOHANG) > 0);
                break;
            case SIGINT:
                // Shutdown
                \PhpTaskDaemon\Daemon\Logger::log('Application (PROCESS) received SIGINT signal (shutting down)', \Zend_Log::DEBUG);
                break;
            default:
                \PhpTaskDaemon\Daemon\Logger::log('Application (PROCESS) received ' . $sig . ' signal (unknown action)', \Zend_Log::DEBUG);
                break;
        }
        exit;
    }

}