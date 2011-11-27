<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager\Process
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager\Process;

class Same extends ProcessAbstract implements ProcessInterface {

    /**
     * Executes a job within the same process.
     */
    public function run() {
        $this->getQueue()->getStatistics()->getIpc()->setVar('executors', array(getmypid()));

        foreach($this->getJobs() as $job) {
            $this->_processTask($job);
        }

        // Remove executor
        $this->getQueue()->getStatistics()->getIpc()->setVar('executors', array());
        $this->getExecutor()->getStatus()->getIpc()->remove();

        \PhpTaskDaemon\Daemon\Logger::log('Finished current set of tasks!', \Zend_Log::INFO);
    }

}
