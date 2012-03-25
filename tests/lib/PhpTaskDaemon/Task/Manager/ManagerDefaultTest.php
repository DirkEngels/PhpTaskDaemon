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
 * @group PhpTaskDaemon-Task-Manager
 */

namespace PhpTaskDaemon\Task\Manager;

class ManagerDefaultTest extends \PHPUnit_Framework_Testcase {
	protected $_manager;
	protected $_executor;
	protected $_queue;
	
	protected function setUp() {
		$this->_executor = new \PhpTaskDaemon\Task\Executor\ExecutorDefault();
		$this->_queue = new \PhpTaskDaemon\Task\Queue\QueueDefault();
		$this->_manager = new \PhpTaskDaemon\Task\Manager\ManagerDefault($this->_executor);
	}
	protected function tearDown() {
		unset($this->_manager);
		unset($this->_executor);
    }
	
	public function testConstructor() {
		$this->assertInstanceOf('\PhpTaskDaemon\Task\Executor\ExecutorAbstract', $this->_manager->getProcess()->getExecutor());
		$this->assertEquals($this->_executor, $this->_manager->getProcess()->getExecutor());
//		$this->assertInstanceOf('\PhpTaskDaemon\Task\Queue\QueueAbstract', $this->_manager->getProcess()->getQueue());
//		$this->assertEquals($this->_queue, $this->_manager->getProcess()->getQueue());
	}
	public function testInitNoArguments() {
		$this->_manager->init();
		$this->assertInstanceOf('\PhpTaskDaemon\Daemon\Pid\Manager', $this->_manager->getPidManager());
		$this->assertEquals(getmypid(), $this->_manager->getPidManager()->getCurrent());
		$this->assertNull($this->_manager->getPidManager()->getParent());
	}
	public function testInitWithParentPid() {
		$this->_manager->init(1234);
		$this->assertInstanceOf('\PhpTaskDaemon\Daemon\Pid\Manager', $this->_manager->getPidManager());
		$this->assertEquals(getmypid(), $this->_manager->getPidManager()->getCurrent());
		$this->assertEquals(1234, $this->_manager->getPidManager()->getParent());
	}
	public function testSetPidManager() {
		$pidManager = new \PhpTaskDaemon\Daemon\Pid\Manager();
		$this->assertNull($this->_manager->getPidManager());
		$this->_manager->setPidManager($pidManager);
		$this->assertEquals($pidManager, $this->_manager->getPidManager());
	}

    public function testSetTimer() {
        $interval = new \PhpTaskDaemon\Task\Manager\Timer\Interval();
        $cron = new \PhpTaskDaemon\Task\Manager\Timer\Cron();
        $this->assertEquals($interval, $this->_manager->getTimer());

        $this->_manager->setTimer($cron);
        $this->assertEquals($cron, $this->_manager->getTimer());

        $this->assertEquals($this->_manager, $this->_manager->setTimer('default timer'));
        $this->assertEquals($interval, $this->_manager->getTimer());
    }

    public function testSetProcess() {
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

        $same = new \PhpTaskDaemon\Task\Manager\Process\Same();
        $child = new \PhpTaskDaemon\Task\Manager\Process\Child();
        $this->assertEquals($same, $this->_manager->getProcess());

        $this->assertEquals($this->_manager, $this->_manager->setProcess($child));
        $this->assertEquals($child, $this->_manager->getProcess());

        $this->assertEquals($this->_manager, $this->_manager->setProcess('default process'));
        $this->assertEquals($same, $this->_manager->getProcess());
    }

	public function testSetQueue() {
//		$this->assertInstanceOf('\PhpTaskDaemon\Task\Queue\QueueAbstract', $this->_manager->getProcess()->getQueue());
		$this->_manager->getProcess()->setQueue($this->_queue);
		$this->assertEquals($this->_queue, $this->_manager->getProcess()->getQueue());
	}
	public function testSetExecutor() {
		$this->assertInstanceOf('\PhpTaskDaemon\Task\Executor\ExecutorAbstract', $this->_manager->getProcess()->getExecutor());
		$this->_manager->getProcess()->setExecutor($this->_executor);
		$this->assertEquals($this->_executor, $this->_manager->getProcess()->getExecutor());
	}
	public function testSetExecutorIncorrectExecutor() {
		$this->_manager->getProcess()->setExecutor('no executor object');
		$this->assertInstanceOf('\PhpTaskDaemon\Task\Executor\ExecutorAbstract', $this->_manager->getProcess()->getExecutor());
		$this->assertEquals($this->_executor, $this->_manager->getProcess()->getExecutor());
	}

    public function testRunManager() {
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

        $manager = $this->getMock('\\PhpTaskDaemon\\Task\\Manager\\ManagerDefault', array('execute'));
        $manager->expects($this->once())
             ->method('execute')
             ->will($this->returnValue(NULL));

        $this->assertNull($manager->runManager());
    }

}
