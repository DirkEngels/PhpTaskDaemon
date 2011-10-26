<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Queue\Statistics
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Queue\Statistics;
use PhpTaskDaemon\Daemon\Config;

/**
 * 
 * The abstract queue statistics class implements methods for setting/change
 * the status count of executed jobs and the number of loaded and processed
 * items in the queue.
 */
abstract class AbstractClass {

    const STATUS_LOADED = 'loaded';
    const STATUS_QUEUED = 'queued';
    const STATUS_RUNNING = 'running';
    const STATUS_DONE = 'done';
    const STATUS_FAILED = 'failed';

    protected $_ipc;


    /**
     * 
     * The constructor sets the shared memory object. A default shared memory
     * object instance will be created when none provided.
     * @param \PhpTaskDaemon\Ipc $ipc
     */
    public function __construct(\PhpTaskDaemon\Daemon\Ipc\AbstractClass $ipc = NULL) {
        if (!is_null($ipc)) {
            $this->setIpc($ipc);
        }
    }


    /**
     * 
     * Unset the shared memory at destruction time.
     */
    public function __destruct() {
        if (is_a($this->_ipc, '\PhpTaskDaemon\Daemon\Ipc\AbstractClass')) {
            $this->_ipc->remove();
            unset($this->_ipc);
        } 
    }


    /**
     *
     * Returns the shared memory object
     * @return PhpTaskDaemon\Ipc
     */
    public function getIpc() {
        if (is_null($this->_ipc)) {
            $ipcClass = '\\PhpTaskDaemon\\Daemon\\Ipc\\' . Config::get()->getOptionValue('global.ipc');
            if (!class_exists($ipcClass)) {
                $ipcClass = '\\PhpTaskDaemon\\Daemon\\Ipc\\None';
            }
            $this->_ipc = new $ipcClass('phptaskdaemond-queue-' . getmypid());

            $this->_initializeStatus(self::STATUS_LOADED);
            $this->_initializeStatus(self::STATUS_QUEUED);
            $this->_initializeStatus(self::STATUS_RUNNING);
            $this->_initializeStatus(self::STATUS_DONE);
            $this->_initializeStatus(self::STATUS_FAILED);
        }

        return $this->_ipc;
    }


    /**
     *
     * Sets a shared memory object
     * @param \PhpTaskDaemon\Daemon\Ipc\None $ipc
     * @return $this
     */
    public function setIpc($ipc) {
        $this->_ipc = $ipc;
        $this->_initializeStatus(self::STATUS_LOADED);
        $this->_initializeStatus(self::STATUS_QUEUED);
        $this->_initializeStatus(self::STATUS_RUNNING);
        $this->_initializeStatus(self::STATUS_DONE);
        $this->_initializeStatus(self::STATUS_FAILED);
        return TRUE;
    }


    /**
     * Returns an array with the number of executed tasks grouped per status.
     * 
     * @param string $status
     * @return array
     */
    public function get($status = NULL) {
        if (is_NULL($status)) {
            return $this->getIpc()->get();
        }
        if (!in_array($status, $this->getIpc()->getKeys())) {
            $this->_initializeStatus($status);
        }
        return $this->getIpc()->getVar($status);
    }


    /**
     * 
     * (Re)Sets a status count
     * @param string $status
     * @param integer $count
     * @return bool
     */
    public function setStatusCount($status = self::STATUS_DONE, $count = 0) {
        if (!in_array($status, $this->getIpc()->getKeys())) {
            $this->_initializeStatus($status);
        }
        return $this->getIpc()->setVar($status, $count);
    }


    /**
     * Increments the statistics for a certain status
     * 
     * @param string $status
     * @return integer
     */
    public function incrementStatus($status = self::STATUS_DONE, $count = 1) {
        return $this->_ipc->incrementVar($status, $count);
    }


    /**
     *
     * (Re)Sets the queue count.
     * @param integer $count
     */
    public function setQueueCount($count = 0) {
        $this->setStatusCount(self::STATUS_QUEUED, $count);
        $this->_ipc->incrementVar(self::STATUS_LOADED, $count);
        return $count;
    }


    /**
     * 
     * Decrements the queue count (after finishing a single job).
     */
    public function decrementQueue($count = 1) {
        return $this->_ipc->decrementVar(self::STATUS_QUEUED, $count);
    }


    /**
     * Initializes the statistics array for a certain status.
     * 
     * @param string $status
     */
    private function _initializeStatus($status) {
        $keys = $this->getIpc()->getKeys();
        if (!in_array($status, array_keys($keys))) {
            $this->getIpc()->setVar($status, 0);
        }
    }

}
