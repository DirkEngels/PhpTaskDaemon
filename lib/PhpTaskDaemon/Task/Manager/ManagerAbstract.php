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
abstract class ManagerAbstract {

    /**
     * Task name
     * @var string
     */
    protected $_name = 'unknown';

    /**
     * 
     * Pid manager object. This class is repsonsible for storing the current, 
     * parent and child process IDs.
     * @var \PhpTaskDaemon\Daemon\Pid\Manager
     */
    protected $_pidManager = null;

    /**
     * Queue object
     * @var \PhpTaskDaemon\Task\Manager\Timer\TimerAbstract
     */
    protected $_timer = null;

    /**
     * Executor object
     * @var \PhpTaskDaemon\Task\Manager\Process\TimerAbstract
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
     * @return \PhpTaskDaemon\Task\Manager\Timer\TimerAbstract
     */
    public function getTimer() {
        if (!is_a($this->_timer, '\PhpTaskDaemon\Task\Manager\Timer\TimerAbstract')) {
            $this->_timer = new \PhpTaskDaemon\Task\Manager\Timer\Interval();
        }
        return $this->_timer;
    }


    /**
     * 
     * Sets the current queue to process.
     * @param \PhpTaskDaemon\Task\Manager\Timer\TimerAbstract $timer
     * @return $this
     */
    public function setTimer($timer) {
        if (!is_a($timer, '\PhpTaskDaemon\Task\Manager\Timer\TimerAbstract')) {
            $timer = new \PhpTaskDaemon\Task\Manager\Timer\Interval();
        }
        $this->_timer = $timer;

        return $this;
    }


    /**
     * 
     * Returns the process object
     * @return \PhpTaskDaemon\Task\Manager\Process\ProcessAbstract
     */
    public function getProcess() {
        if (!is_a($this->_process, '\PhpTaskDaemon\Task\Manager\Process\ProcessAbstract')) {
            $this->_process = new \PhpTaskDaemon\Task\Manager\Process\Same();
            $this->_process->setName($this->_name);
        }
        return $this->_process;
    }


    /**
     * 
     * Sets the current executor object.
     * @param \PhpTaskDaemon\Task\Manager\Process\ProcessAbstract $process
     * @return $this
     */
    public function setProcess($process) {
        if (!($process instanceof \PhpTaskDaemon\Task\Manager\Process\ProcessAbstract)) {
            $process = new \PhpTaskDaemon\Task\Manager\Process\Same();
        }
        $this->_process = $process;
        $this->_process->setName($this->_name);

        return $this;
    }


    /**
     * 
     * Starts the manager
     */
    public function runManager() {
        // Override signal handler
//        $this->_sigHandler = new \PhpTaskDaemon\Daemon\Interrupt\Signal(
//            get_class($this),
//            array(&$this, 'sigHandler')
//        );

        // Set taskname to queue ipc
        $queueIpc = $this->getProcess()->getQueue()->getStatistics()->getIpc();
        $queueIpc->setVar('name', $this->getName());

        $this->execute();
    }


    /**
     * 
     * The sleep function for an interval manager
     */
    protected function _sleep() {
        $sleepTime = \PhpTaskDaemon\Daemon\Config::get()->getOptionValue('timer.interval.time');
        if ($sleepTime <= 0) {
            $sleepTime = 15 * 1000 * 1000;
        }

        // Sleep
        \PhpTaskDaemon\Daemon\Logger::get()->log("Sleeping for : " . $this->_sleepTimeQueue . " micro seconds", \Zend_Log::INFO);
        usleep($sleepTime);
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
                \PhpTaskDaemon\Daemon\Logger::log('Application (TASK) received SIGTERM signal (shutting down)', \Zend_Log::DEBUG);
                break;
            case SIGCHLD:
                // Halt
                \PhpTaskDaemon\Daemon\Logger::log('Application (TASK) received SIGCHLD signal (halting)', \Zend_Log::DEBUG);
                while (pcntl_waitpid(-1, $status, WNOHANG) > 0);
                break;
            case SIGINT:
                // Shutdown
                \PhpTaskDaemon\Daemon\Logger::log('Application (TASK) received SIGINT signal (shutting down)', \Zend_Log::DEBUG);
                break;
            default:
                \PhpTaskDaemon\Daemon\Logger::log('Application (TASK) received ' . $sig . ' signal (unknown action)', \Zend_Log::DEBUG);
                break;
        }
        exit;
    }

}
