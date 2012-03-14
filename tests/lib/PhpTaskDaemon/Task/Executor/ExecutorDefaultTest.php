<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Executor
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Task
 * @group PhpTaskDaemon-Task-Executor
 */

namespace PhpTaskDaemon\Task\Executor;

class ExecutorDefaultTest extends \PHPUnit_Framework_TestCase {
    protected $_executor;
    protected $_status;
    protected $_job;
    
    protected function setUp() {
//        // Stop here and mark this test as incomplete.
//        $this->markTestIncomplete(
//          'This test has not been implemented yet.'
//        );

        $this->_executor = new \PhpTaskDaemon\Task\Executor\ExecutorDefault();
        $this->_job = new \PhpTaskDaemon\Task\Job\JobDefault();

        // Mark empty run function of the base class as executed in the coverage
        $this->_executor->run();
    }

    protected function tearDown() {
//        $ipc = $this->_status->getIpc();
//        if (is_a($ipc, '\PhpTaskDaemon\Daemon\Ipc\IpcAbstract')) {
//            $ipc->remove();
//        }
//        unset($this->_status);
    }


    public function testSetJob() {
        $this->assertNull($this->_executor->getJob());
        $this->_executor->setJob($this->_job);
        $this->assertEquals($this->_job, $this->_executor->getJob());
    }

}
