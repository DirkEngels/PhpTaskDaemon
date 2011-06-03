<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager\Process
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager\Process;

abstract class AbstractClass {

	protected $_jobs = array();

	/**
	 * Gets the job
	 * @return array
	 */
	public function getJobs() {
		return $this->_jobs;
	}

	/**
	 * Sets the jobs
	 * @param array $jobs
	 */
	public function setJobs($jobs) {
		$this->_jobs = $jobs;
	}


    protected function _forkTask($job) {
        // Fork the manager
        $pid = pcntl_fork();
        
        if ($pid == -1) {
            die ('Could not fork.. dunno why not... shutting down... bleep bleep.. blap...');
        } elseif ($pid) {
            // The manager waits later
            $childs++;
        } else {
            // Set manager input and start the manager
            $this->_processTask($job);
            
            // Exit after finishing the forked
            exit;
        }
    } 

}