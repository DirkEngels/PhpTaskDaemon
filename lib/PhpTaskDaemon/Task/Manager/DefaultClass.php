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
class DefaultClass extends AbstractClass implements InterfaceClass {

    public function execute() {
        while (true) {
            // Load Tasks in Queue
            $jobs = $this->getTimer()->getQueue()->load();

            if (count($jobs)==0) {
                \PhpTaskDaemon\Daemon\Logger::get()->log(getmypid() . ": Queue checked: empty!!!", \Zend_Log::DEBUG);
                $this->getProcess()->getExecutor()->updateStatus(100, 'Queue empty');
            } else {
                \PhpTaskDaemon\Daemon\Logger::get()->log(getmypid() . ": Queue loaded: " . count($jobs) . " elements", \Zend_Log::INFO);
                $this->getTimer()->getQueue()->updateQueue(count($jobs));

                while ($job = array_shift($jobs)) {
                    $this->_processTask($job);
                }
                \PhpTaskDaemon\Daemon\Logger::get()->log(getmypid() . ': Queue finished', \Zend_Log::DEBUG);
                $this->getProcess()->getExecutor()->updateStatus(100, 'Queue finished');
            }

            // Take a small rest after so much work. This also prevents
            // manageres from using all resources.
            $this->_sleep();
        }
    }

}