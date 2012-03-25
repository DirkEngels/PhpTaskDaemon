<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Executor
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Task\Executor;
use PhpTaskDaemon\Daemon\Logger;
use PhpTaskDaemon\Daemon\Ipc;
use PhpTaskDaemon\Task\Job;

/**
 * 
 * The executor abstract class implements a method for updating the status and
 * provides setters and getters for the status and job instance.
 */
abstract class ExecutorAbstract {

	/**
     * @var \PhpTaskDaemon\Task\Job\JobAbstract
	 */
	protected $_job = NULL;

    /**
     * Process ID
     * @var integer
     */
    private $_pid;

    /**
     * @var \PhpTaskDaemon\Ipc\IpcAbstract
     */
    protected $_ipc;


    /**
     * The constructor sets the IPC object. A default IPC object instance will
     * be created when none provided.
     * 
     * @param \PhpTaskDaemon\Ipc\IpcAbstract $ipc
     */
    public function __construct(Ipc\IpcAbstract $ipc = NULL) {
        $this->_pid = getmypid();
        if (!is_null($ipc)) {
            $this->setIpc($ipc);
        }
    }


    /**
     * Returns the current job.
     * 
     * @return \PhpTaskDaemon\Task\Job\JobAbstract
     */
    public function getJob() {
        return $this->_job;
    }


    /**
     * Sets the current job.
     * 
     * @param \PhpTaskDaemon\Task\Job\JobAbstract $job
     */
    public function setJob( Job\JobAbstract $job ) {
        $this->_job = $job;
    }


    /**
     * Returns the shared memory object.
     * 
     * @return PhpTaskDaemon\Ipc\IpcAbstract
     */
    public function getIpc() {
        if ( getmypid() != $this->_pid ) {
            if ( ! is_null($this->_ipc) ) {
                $this->_ipc = NULL;
                $this->_pid = getmypid();
            }
        }

        if ( is_null($this->_ipc) ) {
            $this->_ipc = Ipc\IpcFactory::get(
                Ipc\IpcFactory::NAME_EXECUTOR, 
                $this->_pid
            );
        }

        return $this->_ipc;
    }


    /**
     * Sets a shared memory object.
     * 
     * @param \PhpTaskDaemon\Daemon\Ipc\IpcAbstract $ipc
     * @return $this
     */
    public function setIpc( Ipc\IpcAbstract $ipc ) {
        $this->_ipc = $ipc;
        return TRUE;
    }


    /**
     * Resets the current IPC object, so it can be (lazy) loaded again, which
     * is usefull when forking processes.
     * 
     * @return $this 
     */
    public function resetIpc() {
        Logger::log( 'Resetting Status IPC' , \Zend_Log::DEBUG );
        $this->_ipc = NULL;
        return $this;
    }


    /**
     * Resets the current pid, needed for the IPC object.
     *  
     * @param integer $pid
     * @return $this
     */
    public function resetPid($pid = NULL) {
        if ( is_null( $pid ) ) {
            $pid = getmypid();
        }
        Logger::log( 'Resetting Status PID (' . $pid . ')', \Zend_Log::DEBUG );
        $this->_pid = $pid;
        return $this;
    }


    /**
     * 
     * Get one or more status variables. When a key is provided and exists, the
     * corresponding value will be returning. If no key is given, all
     * registered keys and values will be returned.
     * 
     * @param string|NULL $key
     */
    public function getStatus($key = NULL) {
        if ( $key != NULL ) {
            return $this->getIpc()->getVar($key);
        }
        return $this->getIpc()->get();
    }


    /**
     * 
     * Store the status of variable of using a IPC component.
     * 
     * @param integer $percentage
     * @param string $message
     * @return bool
     */
    public function setStatus($percentage, $message = NULL) {
        if ( ( $percentage == 0 ) && ( $message == NULL ) ) {
            $message = 'Initializing task';
        }

        $this->getIpc()->setVar('percentage', $percentage);
        if ($message != NULL) {
            $this->getIpc()->setVar('message', $message);
        }
        return TRUE;
    }

}
