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
        $ipc = new \PhpTaskDaemon\Daemon\Ipc\None(\TMP_PATH . '/test-executor');
        $this->_status = new \PhpTaskDaemon\Task\Executor\Status\StatusDefault($ipc);
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

    public function testConstructorNoArguments() {
        $this->assertNull($this->_executor->getStatus());
    }

    public function testConstructorSingleArgument() {
        $this->_executor = new \PhpTaskDaemon\Task\Executor\ExecutorDefault($this->_status);
        $this->assertInstanceOf('\PhpTaskDaemon\Task\Executor\Status\StatusAbstract', $this->_executor->getStatus());
        $this->assertNull($this->_executor->getJob());
    }

    public function testSetJob() {
        $this->assertNull($this->_executor->getJob());
        $this->_executor->setJob($this->_job);
        $this->assertEquals($this->_job, $this->_executor->getJob());
    }

    public function testSetStatus() {
        $this->assertNull($this->_executor->getStatus());
        $this->_executor->setStatus($this->_status);
        $this->assertEquals($this->_status, $this->_executor->getStatus());
    }

    public function testUpdateStatusNoStatusClassSet() {
        $this->assertNull($this->_executor->getStatus());
        $this->assertFalse($this->_executor->updateStatus('test'));
    }

    /*
    public function testUpdateStatusClassAlreadySet() {
        $this->assertNull($this->_executor->getStatus());
        $this->_executor->setStatus($this->_status);
        $this->assertEquals($this->_status, $this->_executor->getStatus());
        $this->assertFalse($this->_executor->getStatus()->get('percentage'));
        $this->assertFalse($this->_executor->getStatus()->get('message'));
        
        $this->assertTrue($this->_executor->updateStatus(10, 'init'));
        $this->assertEquals(10, $this->_executor->getStatus()->get('percentage'));
        $this->assertEquals('init', $this->_executor->getStatus()->get('message'));

        $this->assertTrue($this->_executor->updateStatus(50, 'still executing'));
        $this->assertEquals(50, $this->_executor->getStatus()->get('percentage'));
        $this->assertEquals('still executing', $this->_executor->getStatus()->get('message'));

        $this->assertTrue($this->_executor->updateStatus(80));
        $this->assertEquals(80, $this->_executor->getStatus()->get('percentage'));
        $this->assertEquals('still executing', $this->_executor->getStatus()->get('message'));
    }
    */

}
