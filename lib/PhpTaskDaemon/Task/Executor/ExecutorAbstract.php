<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Executor
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Task\Executor;

/**
 * 
 * The executor abstract class implements a method for updating the status and
 * provides setters and getters for the status and job instance. 
 */
abstract class ExecutorAbstract {

	protected $_job = NULL;

    protected $_status = NULL;

    /**
     * 
     * Optional a executor status object can be provided. 
     * @param \PhpTaskDaemon\Task\Executor\StatusAbstract $status
     */
    public function __construct($status = NULL) {
        $this->setStatus($status);
    }


    /**
     * 
     * Returns the current job.
     * @return \PhpTaskDaemon\Task\Job\JobAbstract
     */
    public function getJob() {
        return $this->_job;
    }


    /**
     * 
     * Sets the current job
     * @param \PhpTaskDaemon\Task\Job\JobAbstract $job
     */
    public function setJob($job) {
        $this->_job = $job;
    }


    /**
     * 
     * Returns the current status object, if set.
     * @return \PhpTaskDaemon\Task\Executor\Status\StatusAbstract $status
     */
    public function getStatus() {
        return $this->_status;
    }


    /**
     * 
     * Sets the current status object
     * @param \PhpTaskDaemon\Task\Executor\Status\StatusAbstract $status
     */
    public function setStatus($status) {
        $this->_status = $status;
    }


    /**
     * 
     * Updates the status of the current job in shared memory.
     * @param integer $percentage
     * @param string|NULL $message
     * @return bool
     */
    public function updateStatus($percentage, $message = NULL) {
        if ($this->_status != NULL) {
            return $this->_status->set($percentage, $message);
        }
        return FALSE;
    }

}