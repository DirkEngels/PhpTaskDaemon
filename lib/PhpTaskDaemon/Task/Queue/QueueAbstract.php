<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Queue
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Queue;
use PhpTaskDaemon\Daemon\Logger;
use PhpTaskDaemon\Daemon\Ipc;

/**
 * 
 * The base class encapsulates two methods for updating the current queue count
 * and the statistic information about the executed tasks.
 */
abstract class QueueAbstract {

    const STATUS_LOADED = 'loaded';
    const STATUS_QUEUED = 'queued';
    const STATUS_DONE = 'done';
    const STATUS_FAILED = 'failed';

    private $_pid;
    protected $_ipc;


    /**
     *
     * The constructor sets the shared memory object. A default shared memory
     * object instance will be created when none provided.
     * 
     * @param \PhpTaskDaemon\Daemon\Ipc\IpcAbstract $ipc
     */
    public function __construct(\PhpTaskDaemon\Daemon\Ipc\IpcAbstract $ipc = NULL) {
        $this->_pid = getmypid();
        if (!is_null($ipc)) {
            $this->setIpc($ipc);
        }
    }


    /**
     *
     * Returns the shared memory object.
     * 
     * @return PhpTaskDaemon\Daemon\Ipc\IpcAbstract
     */
    public function getIpc() {
        if (getmypid() != $this->_pid) {
            if (!(is_null($this->_ipc))) {
                $this->_ipc = NULL;
            }
        }

        if (is_null($this->_ipc)) {
            $this->setIpc(
                Ipc\IpcFactory::get(Ipc\IpcFactory::NAME_QUEUE, $this->_pid)
            );
        }

        return $this->_ipc;
    }


    /**
     *
     * Sets a shared memory object.
     * 
     * @param \PhpTaskDaemon\Daemon\Ipc\IpcAbstract $ipc
     * @return $this
     */
    public function setIpc($ipc) {
        $this->_ipc = $ipc;
        $this->_initializeIpc();
        return TRUE;
    }


    /**
     * Resets the current IPC object, so it can be (lazy) loaded again, which
     * is usefull when forking processes.
     * 
     * @return $this
     */
    public function resetIpc() {
        Logger::log('Resetting Statistics IPC', \Zend_Log::DEBUG);
        $this->_ipc = NULL;
        return $this;
    }


    /**
     * Resets the current pid, needed for the IPC object.
     * 
     * @param integer $pid
     * @return $this
     */
    public function resetPid($pid) {
        if (is_null($pid)) {
            $pid = getmypid();
        }

        Logger::log('Resetting Statistics PID (' . $pid . ')', \Zend_Log::DEBUG);
        $this->_pid = $pid;
        return $this;
    }


    /**
     * Increments the statistics for a certain status
     *
     * @param string $status
     * @return integer
     */
    public function incrementStatus($status = self::STATUS_DONE, $count = 1) {
        return $this->getIpc()->incrementVar($status, $count);
    }


    /**
     *
     * Decrements the queue count (after finishing a single job).
     * 
     * @param integer $count
     * @return integer
     */
    public function decrementQueue($count = 1) {
        return $this->getIpc()->decrementVar(self::STATUS_QUEUED, $count);
    }    

    /**
     *
     * (Re)Sets a status count.
     * 
     * @param string $status
     * @param integer $count
     * @return bool
     */
    public function setStatusCount($status = self::STATUS_DONE, $count = 0) {
        $ipc = $this->getIpc();
        if (!in_array($status, $ipc->getKeys())) {
            $ipc->setVar($status, 0);
        }
        return $ipc->setVar($status, $count);
    }


    /**
     *
     * (Re)Sets the queue count.
     * 
     * @param integer $count
     */
    public function setQueueCount($count = 0) {
        $this->setStatusCount(self::STATUS_QUEUED, $count);
        $this->getIpc()->setVar(self::STATUS_LOADED, $count);
        return $count;
    }


    /**
     * 
     * Updates the statistic information of executed jobs in the shared memory
     * segment. If no count is given, the current count will be increased by
     * one.
     * 
     * @param integer $status
     * @param integer|NULL $count
     * @return bool
     */
    public function updateStatus($status, $count = 1, $reset = false) {
        if ($reset) {
            return $this->setStatusCount($status, $count);
        } else {
            return $this->incrementStatus($status, $count);
        }
    }


    /**
     * 
     * Updates the current queue information with the current count. If no
     * count is given, the current count will be decreased by one.
     * 
     * @param integer|NULL $count
     * @return boolean
     */
    public function updateQueue($count = NULL) {
        if ($count != NULL) {
            return $this->setQueueCount($count);
        } else {
            return $this->decrementQueue();
        }
    }

    
    /**
     *
     * Initializes the statistics array with default values.
     * 
     * @return bool
     */
    protected function _initializeIpc() {
        $statuses = array(
            self::STATUS_LOADED,
            self::STATUS_QUEUED,
            self::STATUS_DONE,
            self::STATUS_FAILED
        );
        $ipcKeys = $this->_ipc->getKeys();
        foreach($statuses as $status) {
            if (!in_array($status, $ipcKeys)) {
                $this->_ipc->setVar($status, 0);
            }
        }
        return TRUE;
    }

}