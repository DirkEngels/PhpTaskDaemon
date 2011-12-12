<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon;

/**
 * 
 * The run object starts the main process of the daemon. The process is forked 
 * for each task manager.
 *
 */
class Instance {
    /**
     * This variable contains pid manager object
     * @var Pid\Manager $_pidManager
     */
    protected $_pidManager = NULL;

    /**
     * Pid reader object
     * @var Pid\File $_pidFile
     */
    protected $_pidFile = NULL;

    /**
     * Shared memory object
     * @var Ipc\IpcAbstract $_ipc
     */
    protected $_ipc = NULL;

    /**
     * Array with tasks
     * @var array $_tasks
     */
    protected $_tasks = NULL;


    /**
     * 
     * Empty constructor 
     * @param int $parent
     */
    public function __construct() {
        
    }


    /**
     * 
     * Unset variables at destruct to hopefully free some memory. 
     */
    public function __destruct() {
        unset($this->_pidManager);
        unset($this->_pidFile);
        unset($this->_ipc);
        unset($this->_tasks);
    }


    /**
     * Returns the pid manager object
     * @return \PhpTaskDaemon\Daemon\Pid\Manager
     */
    public function getPidManager() {
        if (is_null($this->_pidManager)) {
            $this->_pidManager = new \PhpTaskDaemon\Daemon\Pid\Manager(
                getmypid()
            );
        }

        return $this->_pidManager;
    }


    /**
     * Sets the pid manager object
     * @param $pidManager
     * @return $this
     */
    public function setPidManager(\PhpTaskDaemon\Daemon\Pid\Manager $pidManager) {
        $this->_pidManager = $pidManager;
        return $this;
    }


    /**
     * Returns the pid file object
     * @return \PhpTaskDaemon\Daemon\Pid\File
     */
    public function getPidFile() {
        if (is_null($this->_pidFile)) {
            $pidFile = \TMP_PATH . '/phptaskdaemond.pid';
            $this->_pidFile = new \PhpTaskDaemon\Daemon\Pid\File($pidFile);
        }

        return $this->_pidFile;
    }


    /**
     * Sets the pid file object
     * @param $pidFile
     * @return $this
     */
    public function setPidFile(\PhpTaskDaemon\Daemon\Pid\File $pidFile) {
        $this->_pidFile = $pidFile;
        return $this;
    }


    /**
     * Gets the inter process communication object
     * @return \PhpTaskDaemon\Daemon\Ipc\IpcAbstract
     */
    public function getIpc() {
        if (is_null($this->_ipc)) {
            $ipcClass = '\\PhpTaskDaemon\\Daemon\\Ipc\\' . Config::get()->getOptionValue('global.ipc');
            if (!class_exists($ipcClass)) {
                $ipcClass = '\\PhpTaskDaemon\\Daemon\\Ipc\\None';
            }
            $this->_ipc = new $ipcClass('phptaskdaemond');
        }

        return $this->_ipc;
    }


    /**
     * Sets the inter process communication object
     * @param $ipc \PhpTaskDaemon\Daemon\Ipc\IpcAbstract
     * @return $this
     */
    public function setIpc(\PhpTaskDaemon\Daemon\Ipc\IpcAbstract $ipc) {
        $this->_ipc = $ipc;
        return $this;
    }


    /**
     * 
     * Return the tasks collection object.
     * @return \PhpTaskDaemon\Daemon\Tasks
     */
    public function getTasks() {
        if (is_null($this->_tasks)) {
            $this->_tasks = new \PhpTaskDaemon\Daemon\Tasks();
        }

        return $this->_tasks;
    }


    /**
     * 
     * Sets the tasks collection object
     * @param \PhpTaskDaemon\Daemon\Tasks $tasks
     * @return $this
     */
    public function setTasks(\PhpTaskDaemon\Daemon\Tasks $tasks) {
        $this->_tasks = $tasks;
        return $this;
    }


    /**
     * 
     * This is the public start function of the daemon. It checks the input and
     * available managers before running the daemon.
     */
    public function start() {
        $this->getPidFile()->write($this->getPidManager()->getCurrent());
        $this->getIpc()->setVar('pid', $this->getPidManager()->getCurrent());
        $this->_run();
    }


    /**
     * Dispatches the isRunning method to the Pid\File object
     */
    public function isRunning() {
        return $this->getPidFile()->isRunning();
    }


    /**
     * Stops the running instance
     */
    public function stop() {
        try {
            $pid = $this->getPidFile()->read();

            Logger::log("Killing THIS PID: " . $pid, \Zend_Log::WARN);
            posix_kill($pid, SIGTERM);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        $this->_exit();
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
                Logger::log('Application (DAEMON) received SIGTERM signal (shutting down)', \Zend_Log::DEBUG);
                $this->_exit();
                break;
            case SIGCHLD:
                // Halt
                Logger::log('Application (DAEMON) received SIGCHLD signal (halting)', \Zend_Log::DEBUG);
                while (pcntl_waitpid(-1, $status, WNOHANG) > 0);
                break;
            case SIGINT:
                // Shutdown
                Logger::log('Application (DAEMON) received SIGINT signal (shutting down)', \Zend_Log::DEBUG);
                break;
            default:
                Logger::log('Application (DAEMON) received ' . $sig . ' signal (unknown action)', \Zend_Log::DEBUG);
                break;
        }
    }


    /**
     * 
     * This is the main function for running the daemon!
     */
    protected function _run() {
        // All OK.. Let's go
        declare(ticks = 1);

        if (count($this->_tasks->getManagers())==0) {
            Logger::log("No daemon tasks found", \Zend_Log::INFO);
            $this->_exit();
        }
        Logger::log("Found " . count($this->_tasks->getManagers()) . " daemon task managers", \Zend_Log::NOTICE);

        $this->getIpc()->setVar('processes', array());
        $managers = $this->_tasks->getManagers();
        foreach ($managers as $manager) {
            Logger::log("Starting manager: "  . $manager->getName(), \Zend_Log::NOTICE);
            try {
                $this->_forkManager($manager);
            } catch (Exception $e) {
                echo $e->getMessage();
                Logger::log($e->getMessage(), \Zend_Log::CRIT);
                $this->_exit();
            }
        }

        // Default sigHandler
        Logger::log("Setting default signal interrupt handler", \Zend_Log::DEBUG);
        $this->_sigHandler = new Interrupt\Signal(
            'Main Daemon',
            array(&$this, 'sigHandler')
        );

        // Write pids to shared memory
        $this->getIpc()->setVar('processes', $this->getPidManager()->getChilds());

        // Wait till all childs are done
        Logger::log("Waiting for childs to complete", \Zend_Log::NOTICE);
        while (pcntl_waitpid(0, $status) != -1) {
            $status = pcntl_wexitstatus($status);
        }
        Logger::log("Running done.", \Zend_Log::NOTICE);

        $this->getPidFile()->unlink();

        $this->_exit();
    }


    /**
     * 
     * Fork the managers to be processed in the background. The foreground task
     * is used to add gearman tasks to the queue for the background gearman 
     * managers
     */
    private function _forkManager($manager)
    {
        $pid = pcntl_fork();
        if ($pid === -1) {
            // Error: Throw exception: Fork Failed
            Logger::log('Managers could not be forked!!!', \Zend_Log::CRIT);
            throw new \PhpTaskDaemon\Daemon\Exception\ForkFailed();

        } elseif ($pid) {
            // Parent
            $managerName = substr(substr(get_class($manager), 6), 0, -8);
            $this->getPidManager()->addChild($pid);

        } else {
            // Child 
            $newPid = getmypid();
            $this->getPidManager()->forkChild($newPid);
            $manager->init($this->getPidManager()->getParent());

            $statistics = new \PhpTaskDaemon\Task\Queue\Statistics\StatisticsDefault();
            $manager->getProcess()->getQueue()->setStatistics($statistics);

            $status = new \PhpTaskDaemon\Task\Executor\Status\StatusDefault();
            $manager->getProcess()->getExecutor()->setStatus($status);

            Logger::log('Manager forked (PID: ' . $newPid . ') !!!', \Zend_Log::DEBUG);
            $manager->runManager();
            $this->_exit();
        }
    }

    protected function _exit() {
        exit;
    }

}
