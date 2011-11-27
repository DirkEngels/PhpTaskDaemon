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


    /**
     * Spawns multiple child processes in order to execute proceses in
     * parallel.
     */
    public function run() {
        $currentChilds = 0;

        $jobs = $this->getJobs();
        while(count($jobs)>0) {
            if ($currentChilds<$this->_maxProcess) {
                $job = array_shift($jobs);

                $this->_forkTask($job);
            }
        }

        for ($i = $currentChilds; $i<$this->_maxProcess; $i++) {
            $this->_forkTask($job);
            $currentChilds++;
        }
    }

}
