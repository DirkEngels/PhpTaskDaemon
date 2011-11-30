<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Queue\Statistics
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Queue\Statistics;
use PhpTaskDaemon\Daemon\Ipc\IpcFactory;
use PhpTaskDaemon\Daemon\Config;
use PhpTaskDaemon\Daemon\Logger;


/**
 *
 * The abstract queue statistics class implements methods for setting/change
 * the status count of executed jobs and the number of loaded and processed
 * items in the queue.
 */
abstract class StatisticsAbstract {

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
     * Returns the shared memory object
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
                IpcFactory::get(IpcFactory::NAME_QUEUE, $this->_pid)
            );
        }

        return $this->_ipc;
    }


    /**
     *
     * Sets a shared memory object
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
     * @return $this
     */
    public function resetIpc() {
        Logger::log('Resetting Statistics IPC', \Zend_Log::DEBUG);
        $this->_ipc = NULL;
        return $this;
    }


    /**
     * Resets the current pid, needed for the IPC object.
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
     * Returns an array with the number of executed tasks grouped per status.
     *
     * @param string $status
     * @return array
     */
    public function get($status = self::STATUS_QUEUED) {
        $ipc = $this->getIpc();
        if (is_NULL($status)) {
            return $ipc->get();
        }

        if (!in_array($status, $ipc->getKeys())) {
            $ipc->setVar($status, 0);
        }
        return $ipc->getVar($status);
    }


    /**
     *
     * (Re)Sets a status count
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
     * (Re)Sets the queue count.
     * @param integer $count
     */
    public function setQueueCount($count = 0) {
        $this->setStatusCount(self::STATUS_QUEUED, $count);
        $this->getIpc()->setVar(self::STATUS_LOADED, $count);
        return $count;
    }


    /**
     *
     * Decrements the queue count (after finishing a single job).
     */
    public function decrementQueue($count = 1) {
        return $this->getIpc()->decrementVar(self::STATUS_QUEUED, $count);
    }


    /**
     *
     * Initializes the statistics array with default values.
     */
    protected function _initializeIpc() {
        $this->_ipc->setVar(self::STATUS_LOADED, 0);
        $this->_ipc->setVar(self::STATUS_QUEUED, 0);
        $this->_ipc->setVar(self::STATUS_DONE, 0);
        $this->_ipc->setVar(self::STATUS_FAILED, 0);
    }

}
