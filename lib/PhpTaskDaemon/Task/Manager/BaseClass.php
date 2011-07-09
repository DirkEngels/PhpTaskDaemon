<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Task\Manager;

/**
 * 
 * The manager base class does notting, but is defined for unit testing
 * purposes.
 */
class BaseClass extends AbstractClass implements InterfaceClass {

    public function execute() {
        echo "Running\n";
        while (true) {
            // Load Tasks in Queue
            $jobs = $this->getTrigger()->getQueue()->load();

            if (count($jobs)==0) {
                \PhpTaskDaemon\Daemon\Logger::get()->log("Queue checked: empty!!!", \Zend_Log::DEBUG);
                $this->getProcess()->getExecutor()->updateStatus(100, 'Queue empty');
            } else {
                \PhpTaskDaemon\Daemon\Logger::get()->log("Queue loaded: " . count($jobs) . " elements", \Zend_Log::INFO);
                $this->getTrigger()->getQueue()->updateQueue(count($jobs));

                while ($job = array_shift($jobs)) {
                    $this->_processTask($job);
                }
                \PhpTaskDaemon\Daemon\Logger::get()->log('Queue finished', \Zend_Log::DEBUG);
                $this->getProcess()->getExecutor()->updateStatus(100, 'Queue finished');
            }

            // Take a small rest after so much work. This also prevents
            // manageres from using all resources.
            $this->_sleep();
        }

        echo "Done\n";
    }

}