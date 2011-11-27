<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Executor\Status
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Executor\Status;
use PhpTaskDaemon\Daemon\Ipc\IpcFactory;
use PhpTaskDaemon\Daemon\Config;
use PhpTaskDaemon\Daemon\Logger;

/**
 * 
 * The abstract class encapsulate a set of methods of the Ipc class. 
 *
 */
abstract class AbstractClass {
    /**
     * @var \PhpTaskDaemon\Ipc\AbstractClass
     */
    protected $_ipc;

    /**
     * Process ID
     * @var integer
     */
    private $_pid;


    /**
     * 
     * The constructor sets the shared memory object. A default shared memory
     * object instance will be created when none provided.
     * @param \PhpTaskDaemon\Ipc $ipc
     */
    public function __construct(\PhpTaskDaemon\Daemon\Ipc\AbstractClass $ipc = NULL) {
        $this->_pid = getmypid();
        if (!is_null($ipc)) {
            $this->setIpc($ipc);
        }
    }


//    /**
//     * 
//     * Unset the shared memory at destruction time.
//     */
//    public function __destruct() {
//        unset($this->_ipc); 
//    }


    /**
     *
     * Returns the shared memory object
     * @return PhpTaskDaemon\Ipc\AbstractClass
     */
    public function getIpc() {
        if (getmypid() != $this->_pid) {
            if (!(is_null($this->_ipc))) {
                $this->_ipc = NULL;
                $this->_pid = getmypid();
            }
        }

        if (is_null($this->_ipc)) {
            $this->_ipc = IpcFactory::get(IpcFactory::NAME_EXECUTOR, $this->_pid);
        }

        return $this->_ipc;
    }


    /**
     *
     * Sets a shared memory object
     * @param \PhpTaskDaemon\Daemon\Ipc\Ipc $ipc
     * @return $this
     */
    public function setIpc($ipc) {
        $this->_ipc = $ipc;
        return TRUE;
    }


    /**
     * Resets the current IPC object, so it can be (lazy) loaded again, which
     * is usefull when forking processes.
     * @return $this 
     */
    public function resetIpc() {
        Logger::log('Resetting Status IPC', \Zend_Log::DEBUG);
        $this->_ipc = NULL;
        return $this;
    }


    /**
     * Resets the current pid, needed for the IPC object. 
     * @param integer $pid
     * @return $this
     */
    public function resetPid($pid = NULL) {
        if (is_null($pid)) {
            $pid = getmypid();
        }
        Logger::log('Resetting Status PID (' . $pid . ')', \Zend_Log::DEBUG);
        $this->_pid = $pid;
        return $this;
    }


    /**
     * 
     * Get one or more status variables. When a key is provided and exists, the
     * corresponding value will be returning. If no key is given, all
     * registered keys and values will be returned. 
     * @param string|NULL $key
     */
    public function get($key = NULL) {
        if ($key != NULL) {
            return $this->getIpc()->getVar($key);
        }
        return $this->getIpc()->getKeys();
    }


    /**
     * 
     * Store the status of variable of using a shared memory segment. 
     * @param integer $percentage
     * @param string $message
     * @return bool
     */
    public function set($percentage, $message = NULL) {
        $this->getIpc()->setVar('percentage', $percentage);
        if ($message != NULL) {
            $this->getIpc()->setVar('message', $message);
        }
        return TRUE;
    }

}