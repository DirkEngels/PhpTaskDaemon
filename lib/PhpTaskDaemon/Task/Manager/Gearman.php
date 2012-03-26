<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager;

class Gearman extends ManagerAbstract implements ManagerInterface {

    /**
     * Starts a gearman worker.
     */
    public function execute() {
        $gearmanWorker= new \GearmanWorker();
        $gearmanWorker->addServer();

        // Register executor dispather
        $gearmanWorker->addFunction( 'test', array( $this, 'processGearmanTask' ) );

        // Start the gearman worker
        while( $gearmanWorker->work() ) {
            if ( $gearmanWorker->returnCode() != GEARMAN_SUCCESS ) {
                echo "return_code: " . $gearmanWorker->returnCode();
                break;
            }
        }
    }


    /**
     * Accept the gearman input and process the job.
     * 
     * @param \PhpTaskDaemon\Task\Job\JobAbstract $gearmanJob
     */
    public function processGearmanTask( $gearmanJob ) {
        $data = unserialize( $gearmanJob->workload() );
        if ( ! is_array( $data ) ) {
            $data = array( 'data' => $data );
        }
        $job = new \PhpTaskDaemon\Task\Job\JobDefault(
            'gearman-' . getmypid(),
            new \PhpTaskDaemon\Task\Job\Data\DataDefault(
                $data
            )
        );
        return $this->getProcess()
            ->setJobs( array( $job ) )
            ->run();
    }

}