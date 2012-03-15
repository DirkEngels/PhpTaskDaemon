\<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager\Process
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager\Process;

class Parallel extends Child {

    protected $_maxProcess = 3;

    /**
     * Overrides the parent process bby 
     * @see PhpTaskDaemon\Task\Manager\Process.Child::runParent()
     */
    public function runParent($pid) {
        // The manager waits later
        \PhpTaskDaemon\Daemon\Logger::log('Spawning child process: ' . $pid . '!', \Zend_Log::NOTICE);

        try {
            if ($this->_childCount >= $this->_maxProcess) {
                $pid = getmypid();

                while ($pid != -1) {
                    $pid = pcntl_wait($status);
                    $status = pcntl_wexitstatus($status);
                    echo "Child $status completed\n";
                    $this->_childCount--;
                }
            }

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}
