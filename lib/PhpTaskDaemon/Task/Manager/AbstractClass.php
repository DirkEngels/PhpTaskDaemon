<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager;

/**
 * 
 * This is the abstract class of a Daemon_Manager. It provides the basic 
 * methods needed for almost all managers. 
 */
abstract class AbstractClass {

    /**
     * 
     * Pid manager object. This class is repsonsible for storing the current, 
     * parent and child process IDs.
     * @var \PhpTaskDaemon\Daemon\Pid\Manager
     */
    protected $_pidManager = null;

    /**
     * Queue object
     * @var \PhpTaskDaemon\Task\Manager\Timer\AbstractClass
     */
    protected $_timer = null;

    /**
     * Executor object
     * @var \PhpTaskDaemon\Task\Manager\Process\AbstractClass
     */
    protected $_process = null;

    /**
     * Time to wait in milliseconds before running the next task.
     * 
     * @var integer
     */
    protected $_sleepTimeExecutor = 10;

    /**
     * Time to wait in milliseconds before loading the queue again.
     * 
     * @var integer
     */
    protected $_sleepTimeQueue = 3000000;


    /**
     * 
     * Initializes the pid manager
     * @param int $parentPid
     */
    public function init($parentPid = null) {
        $this->_pidManager = new \PhpTaskDaemon\Daemon\Pid\Manager(
            getmypid(), 
            $parentPid
        );
    }


    /**
     * 
     * Returns the pid manager of the task manager
     * @return \PhpTaskDaemon\Pid\Manager
     */
    public function getPidManager() {
        return $this->_pidManager;
    }


    /**
     * 
     * Sets the pid manager of the task manager
     * @param \PhpTaskDaemon\Pid\Manager $pidManager
     * @return $this
     */
    public function setPidManager(\PhpTaskDaemon\Daemon\Pid\Manager $pidManager) {
        $this->_pidManager = $pidManager;
        return $this;
    }


    /**
     * 
     * Returns the current loaded queue array
     * @return \PhpTaskDaemon\Task\Manager\Timer\AbstractClass
     */
    public function getTimer() {
        if (!is_a($this->_timer, '\PhpTaskDaemon\Task\Manager\Timer\AbstractClass')) {
            $this->_timer = new \PhpTaskDaemon\Task\Manager\Timer\Interval();
        }
        return $this->_timer;
    }


    /**
     * 
     * Sets the current queue to process.
     * @param \PhpTaskDaemon\Task\Manager\Timer\AbstractClass $timer
     * @return $this
     */
    public function setTimer($timer) {
        if (!is_a($timer, '\PhpTaskDaemon\Task\Manager\Timer\AbstractClass')) {
            $timer = new \PhpTaskDaemon\Task\Manager\Timer\Interval();
        }
        $this->_timer = $timer;

        return $this;
    }


    /**
     * 
     * Returns the process object
     * @return \PhpTaskDaemon\Task\Manager\Process\AbstractClass
     */
    public function getProcess() {
        if (!is_a($this->_process, '\PhpTaskDaemon\Task\Manager\Process\AbstractClass')) {
            $this->_process = new \PhpTaskDaemon\Task\Manager\Process\Same();
        }
        return $this->_process;
    }


    /**
     * 
     * Sets the current executor object.
     * @param \PhpTaskDaemon\Task\Manager\Process\AbstractClass $process
     * @return $this
     */
    public function setProcess($process) {
        if (!($process instanceof \PhpTaskDaemon\Task\Manager\Process\AbstractClass)) {
            $process = new \PhpTaskDaemon\Task\Manager\Process\Same();
        }
        $this->_process = $process;

        return $this;
    }


    /**
     * 
     * Starts the manager
     */
    public function runManager() {
        // Override signal handler
        $this->_sigHandler = new \PhpTaskDaemon\Daemon\Interrupt\Signal(
            get_class($this),
            array(&$this, 'sigHandler')
        );

        $this->execute();
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
                \PhpTaskDaemon\Daemon\Logger::get()->log('Application (TASK) received SIGTERM signal (shutting down)', \Zend_Log::DEBUG);
                break;
            case SIGCHLD:
                // Halt
                \PhpTaskDaemon\Daemon\Logger::get()->log('Application (TASK) received SIGCHLD signal (halting)', \Zend_Log::DEBUG);        
                while (pcntl_waitpid(-1, $status, WNOHANG) > 0);
                break;
            case SIGINT:
                // Shutdown
                \PhpTaskDaemon\Daemon\Logger::get()->log('Application (TASK) received SIGINT signal (shutting down)', \Zend_Log::DEBUG);
                break;
            default:
                \PhpTaskDaemon\Daemon\Logger::get()->log('Application (TASK) received ' . $sig . ' signal (unknown action)', \Zend_Log::DEBUG);
                break;
        }
        exit;
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
        $executor = $this->getProcess()->getExecutor();
        $executor->setJob($job);
        $queue = $this->getTimer()->getQueue();

        // Update Status before and after running the task
        $executor->updateStatus(0);
        $job = $executor->run();
        $executor->updateStatus(100);

        // Log and sleep for a while
        usleep($this->_sleepTimeExecutor);
        \PhpTaskDaemon\Daemon\Logger::get()->log(getmypid() . ': ' . $job->getOutput()->getVar('returnStatus') . ": " . $job->getJobId(), \Zend_Log::DEBUG);            
        $queue->updateStatistics($job->getOutput()->getVar('returnStatus'));

        // Reset status and decrement queue
        $executor->updateStatus(0);
        $queue->updateStatistics($job->getOutput()->getVar('returnStatus'));
        $queue->updateQueue();

        return $job->getOutput()->getVar('returnStatus');
    }


    /**
     * 
     * The sleep function for an interval manager
     */
    protected function _sleep() {
        // Sleep
        \PhpTaskDaemon\Daemon\Logger::get()->log("Sleeping for : " . $this->_sleepTimeQueue . " micro seconds", \Zend_Log::DEBUG);
        usleep($this->_sleepTimeQueue);
    }

}
