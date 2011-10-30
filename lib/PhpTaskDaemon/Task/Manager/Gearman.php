<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager;

class Gearman extends AbstractClass implements InterfaceClass {

    /**
     * (non-PHPdoc)
     * @see lib/PhpTaskDaemon/Task/Manager/PhpTaskDaemon\Task\Manager.InterfaceClass::execute()
     */
    public function execute() {
        $gearmanWorker= new \GearmanWorker();
        $gearmanWorker->addServer();

        // Register executor dispather
        $gearmanWorker->addFunction('test', array($this, 'processGearmanTask'));

        // Start the gearman worker
        while($gearmanWorker->work()) {
            if ($gearmanWorker->returnCode() != GEARMAN_SUCCESS) {
                echo "return_code: " . $gearmanWorker->returnCode();
                break;
            }
        }
    }


    /**
     * 
     * Accept the gearman input and process the job.
     * @param unknown_type $gearmanJob
     */
    public function processGearmanTask($gearmanJob) {
        $data = $gearmanJob->workload();
        $job = new \PhpTaskDaemon\Task\Job\DefaultClass();
        $job->setInput(
            new \PhpTaskDaemon\Task\Job\Data\DefaultClass(
                array('gearmanData' => $data)
            )
        );

        return $this->_processTask($job);
    }

}