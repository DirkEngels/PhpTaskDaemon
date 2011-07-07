<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager\Process
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager\Process;

class Child extends AbstractClass implements InterfaceClass {

	/**
	 * Forks the task to a seperate process
	 * @param \PhpTaskDaemon\Task\Job $job
	 */
    protected function run() {
        // Fork the manager
        $pid = pcntl_fork();
        
        if ($pid == -1) {
        	$err = 'Could not fork.. dunno why not... shutting down... bleep bleep.. blap...';
        	\PhpTaskDaemon\Daemon\Logger::get()->log($err, \Zend_Log::CRIT);
            die ($err);
        } elseif ($pid) {
            // The manager waits later
            $childs++;

        } else {
        	foreach($this->getJobs() as $job) {        		
	            // Set manager input and start the manager
	            $this->_forkTask($this->getJob());
	            
        	}
            \PhpTaskDaemon\Daemon\Logger::get()->log('Finished current set of tasks! Child exits!', \Zend_Log::INFO);
        	
            // Exit after finishing the forked
            exit;
        }
    } 

}