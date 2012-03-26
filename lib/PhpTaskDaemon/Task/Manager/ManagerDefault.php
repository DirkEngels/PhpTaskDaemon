<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Task\Manager;
use PhpTaskDaemon\Task\Job;

/**
 * The manager base class does notting, but is defined for unit testing
 * purposes.
 */
class ManagerDefault extends ManagerAbstract implements ManagerInterface {

    public function execute() {
        while ( true ) {
            // Re-initialize the IPC components
            $process = $this->getProcess();
            $process->getExecutor()->resetIpc();
            $process->getQueue()->resetIpc();

            // Load Tasks in Queue
            $jobs = $this->getProcess()->getQueue()->load();

            if (count($jobs)==0) {
                \PhpTaskDaemon\Daemon\Logger::get()->log( getmypid() . ": Queue checked: empty!!!", \Zend_Log::DEBUG );

            } else {
                $this->getProcess()->getQueue()->updateQueue( count( $jobs ) );
                \PhpTaskDaemon\Daemon\Logger::get()->log( getmypid() . ": Queue loaded: " . count($jobs) . " elements", \Zend_Log::NOTICE );

                // Pass the jobs to the Process compomnent
                $this->getProcess()->setJobs( $jobs )->run();
                \PhpTaskDaemon\Daemon\Logger::get()->log( getmypid() . ': Queue finished', \Zend_Log::DEBUG );
            }

            // Take a small rest after so much work. This also prevents
            // manageres from using all resources.
            $this->_sleep();
        }
    }

}
