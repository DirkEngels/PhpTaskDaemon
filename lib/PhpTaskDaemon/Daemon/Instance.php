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
     * @var Ipc\AbstractClass $_ipc
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
     * @return \PhpTaskDaemon\Daemon\Ipc\AbstractClass
     */
    public function getIpc() {
        if (is_null($this->_ipc)) {
            $this->_ipc = new \PhpTaskDaemon\Daemon\Ipc\None('phptaskdaemond');
        }

        return $this->_ipc;
    }


    /**
     * Sets the inter process communication object
     * @param $ipc \PhpTaskDaemon\Daemon\Ipc\AbstractClass
     * @return $this
     */
    public function setIpc(\PhpTaskDaemon\Daemon\Ipc\AbstractClass $ipc) {
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
        $this->_pidFile->write($this->_pidManager->getCurrent());
        $this->_run();
    }


    /**
     * Dispatches the isRunning method to the Pid\File object
     */
    public function isRunning() {
        return $this->_pidFile->isRunning();
    }


    public function stop() {
        try {
            $pidFile = new Pid\File($pidFile = \TMP_PATH . '/phptaskdaemond.pid');
            $pid = $pidFile->read();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }

        \PhpTaskDaemon\Daemon\Logger::get()->log("Killing THIS PID: " . $pid, \Zend_Log::WARN);
        posix_kill($pid, SIGTERM);

        /*
        echo "THIS PID: " . $this->_pidManager->getCurrent() . "\n";
        echo "Child PIDs\n";
        $childs = $this->_pidManager->getChilds();
        echo var_dump($childs);
        foreach($childs as $child) {
            echo " - " . $child . "\n";
        }
        echo "\n";
        */
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
                \PhpTaskDaemon\Daemon\Logger::get()->log('Application (DAEMON) received SIGTERM signal (shutting down)', \Zend_Log::DEBUG);
                exit;
                break;
            case SIGCHLD:
                // Halt
                \PhpTaskDaemon\Daemon\Logger::get()->log('Application (DAEMON) received SIGCHLD signal (halting)', \Zend_Log::DEBUG);
                while (pcntl_waitpid(-1, $status, WNOHANG) > 0);
                break;
            case SIGINT:
                // Shutdown
                \PhpTaskDaemon\Daemon\Logger::get()->log('Application (DAEMON) received SIGINT signal (shutting down)', \Zend_Log::DEBUG);
                break;
            default:
                \PhpTaskDaemon\Daemon\Logger::get()->log('Application (DAEMON) received ' . $sig . ' signal (unknown action)', \Zend_Log::DEBUG);
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
            \PhpTaskDaemon\Daemon\Logger::get()->log("No daemon tasks found", \Zend_Log::INFO);
            exit;
        }
        \PhpTaskDaemon\Daemon\Logger::get()->log("Found " . count($this->_tasks->getManagers()) . " daemon task managers", \Zend_Log::INFO);

        $this->_ipc->setVar('childs', array());
        $managers = $this->_tasks->getManagers();
        foreach ($managers as $manager) {
            \PhpTaskDaemon\Daemon\Logger::get()->log("Forking manager: "  . get_class($manager), \Zend_Log::INFO);
            try {
                $this->_forkManager($manager);
            } catch (Exception $e) {
                \PhpTaskDaemon\Daemon\Logger::get()->log($e->getMessage(), \Zend_Log::CRIT);
                exit;
            }
        }

        // Default sigHandler
        \PhpTaskDaemon\Daemon\Logger::get()->log("Setting default signal interrupt handler", \Zend_Log::DEBUG);
        $this->_sigHandler = new Interrupt\Signal(
            'Main Daemon',
            array(&$this, 'sigHandler')
        );

        // Write pids to shared memory
        $this->_ipc->setVar('childs', $this->_pidManager->getChilds());

        // Wait till all childs are done
        \PhpTaskDaemon\Daemon\Logger::get()->log("Waiting for childs to complete", \Zend_Log::NOTICE);
        while (pcntl_waitpid(0, $status) != -1) {
            $status = pcntl_wexitstatus($status);
        }
        \PhpTaskDaemon\Daemon\Logger::get()->log("Running done.", \Zend_Log::NOTICE);

        $this->_pidFile->unlink();
        $this->_ipc->remove();

        exit;
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
            // Error
            \PhpTaskDaemon\Daemon\Logger::get()->log('Managers could not be forked!!!', \Zend_Log::CRIT);

            // Throw exception: Fork Failed
            throw new \PhpTaskDaemon\Daemon\Exception\ForkFailed();

            return FALSE;

        } elseif ($pid) {
            // Parent
            $this->_pidManager->addChild($pid);
            $managerName = substr(substr(get_class($manager), 6), 0, -8);
            $this->_ipc->setVar('status-'. $pid, $managerName);

        } else { 
            $newPid = getmypid();
            $this->_pidManager->forkChild($newPid);
            $manager->init($this->_pidManager->getParent());

            $statistics = new \PhpTaskDaemon\Task\Queue\Statistics\DefaultClass();
            $manager->getTrigger()->getQueue()->setStatistics($statistics);

            $status = new \PhpTaskDaemon\Task\Executor\Status\DefaultClass();
            $manager->getProcess()->getExecutor()->setStatus($status);

            \PhpTaskDaemon\Daemon\Logger::get()->log('Manager forked (PID: ' . $newPid . ') !!!', \Zend_Log::DEBUG);
            $manager->runManager();
            exit;
        }
    }

}