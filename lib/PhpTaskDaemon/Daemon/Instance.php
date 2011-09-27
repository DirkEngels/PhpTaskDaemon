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
     * Array with managers
     * @var array $_managers
     */
    protected $_managers = array();


    /**
     * 
     * The construction has one optional argument containing the parent process
     * ID. 
     * @param int $parent
     */
    public function __construct() {
        $pidFile = \TMP_PATH . '/phptaskdaemond.pid';
        $this->_pidManager = new \PhpTaskDaemon\Daemon\Pid\Manager(getmypid());
        $this->_pidFile = new \PhpTaskDaemon\Daemon\Pid\File($pidFile);
        
        $this->_ipc = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory('phptaskdaemond');
    }


    /**
     * 
     * Unset variables at destruct to hopefully free some memory. 
     */
    public function __destruct() {
        unset($this->_pidManager);
        unset($this->_pidFile);
        unset($this->_ipc);
    }


    /**
     * 
     * Returns the log file to use. It tries a few possible options for
     * retrieving or composing a valid logfile.
     * @return string
     */
    protected function _getLogFile() {
        $logFile = TMP_PATH . '/phptaskdaemond.log';
        return $logFile;
    }


    /**
     * 
     * Return the loaded manager objects.
     * @return array
     */
    public function getManagers() {
        return $this->_managers;
    }


    /**
     * 
     * Adds a manager object to the managers stack
     * @param \PhpTaskDaemon\Task\Manager\AbstractClass $manager
     * @return bool
     */
    public function addManager($manager) {
        return array_push($this->_managers, $manager);
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

        if (count($this->_managers)==0) {
            \PhpTaskDaemon\Daemon\Logger::get()->log("No daemon tasks found", \Zend_Log::INFO);
            exit;
        }
        \PhpTaskDaemon\Daemon\Logger::get()->log("Found " . count($this->_managers) . " daemon tasks", \Zend_Log::INFO);

        $this->_ipc->setVar('childs', array());
        foreach ($this->_managers as $manager) {
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

            $statistics = new \PhpTaskDaemon\Task\Queue\Statistics\BaseClass();
            $manager->getTrigger()->getQueue()->setStatistics($statistics);

            $status = new \PhpTaskDaemon\Task\Executor\Status\BaseClass();
            $manager->getProcess()->getExecutor()->setStatus($status);

            \PhpTaskDaemon\Daemon\Logger::get()->log('Manager forked (PID: ' . $newPid . ') !!!', \Zend_Log::DEBUG);
            $manager->runManager();
            exit;
        }
    }

}