<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager\Process
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager\Process;

class Parallel extends ProcessAbstract implements ProcessInterface {

    protected $_maxProcess = 3;

    public function runParent($pid) {
        // The manager waits later
        \PhpTaskDaemon\Daemon\Logger::log('Spawning child process: ' . $pid . '!', \Zend_Log::NOTICE);

        try {
            $res = pcntl_waitpid($pid, $status);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}
