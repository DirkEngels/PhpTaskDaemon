<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager\Process
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager\Process;
use PhpTaskDaemon\Daemon\Logger;

class Child extends AbstractClass implements InterfaceClass {

    /**
     * Forks the task to a seperate process
     * @param \PhpTaskDaemon\Task\Job $job
     */
    public function run() {
        // Fork the manager
        $pid = pcntl_fork();

        if ($pid == -1) {
            $err = 'Could not fork.. dunno why not... shutting down... bleep bleep.. blap...';
            \PhpTaskDaemon\Daemon\Logger::log($err, \Zend_Log::CRIT);
            die ($err);
        } elseif ($pid) {
            // The manager waits later

        } else {
            $jobs = $this->getJobs();
            foreach($jobs as $job) {
                // Set manager input and start the manager
//                $this->_forkTask($job);
            }
            Logger::log('Finished current set of tasks! Child exits!', \Zend_Log::INFO);
            exit;
        }
    } 

}