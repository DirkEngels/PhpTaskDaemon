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

class DefaultClassTest extends \PHPUnit_Framework_Testcase {
	protected $_manager;
	protected $_executor;
	protected $_queue;
	
	protected function setUp() {
		$this->_executor = new \PhpTaskDaemon\Task\Executor\DefaultClass();
		$this->_queue = new \PhpTaskDaemon\Task\Queue\DefaultClass();
		$this->_manager = new \PhpTaskDaemon\Task\Manager\DefaultClass($this->_executor);
	}
	protected function tearDown() {
		unset($this->_manager);
		unset($this->_executor);
    }
	
	public function testConstructor() {
		$this->assertInstanceOf('\PhpTaskDaemon\Task\Executor\AbstractClass', $this->_manager->getProcess()->getExecutor());
		$this->assertEquals($this->_executor, $this->_manager->getProcess()->getExecutor());
//		$this->assertInstanceOf('\PhpTaskDaemon\Task\Queue\AbstractClass', $this->_manager->getTrigger()->getQueue());
//		$this->assertEquals($this->_queue, $this->_manager->getTrigger()->getQueue());
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

    public function testSetTrigger() {
        $interval = new \PhpTaskDaemon\Task\Manager\Trigger\Interval();
        $cron = new \PhpTaskDaemon\Task\Manager\Trigger\Cron();
        $this->assertEquals($interval, $this->_manager->getTrigger());
        $this->_manager->setTrigger($cron);
        $this->assertEquals($cron, $this->_manager->getTrigger());
    }

	public function testSetQueue() {
//		$this->assertInstanceOf('\PhpTaskDaemon\Task\Queue\AbstractClass', $this->_manager->getTrigger()->getQueue());
		$this->_manager->getTrigger()->setQueue($this->_queue);
		$this->assertEquals($this->_queue, $this->_manager->getTrigger()->getQueue());
	}
	public function testSetExecutor() {
		$this->assertInstanceOf('\PhpTaskDaemon\Task\Executor\AbstractClass', $this->_manager->getProcess()->getExecutor());
		$this->_manager->getProcess()->setExecutor($this->_executor);
		$this->assertEquals($this->_executor, $this->_manager->getProcess()->getExecutor());
	}
	public function testSetExecutorIncorrectExecutor() {
		$this->_manager->getProcess()->setExecutor('no executor object');
		$this->assertInstanceOf('\PhpTaskDaemon\Task\Executor\AbstractClass', $this->_manager->getProcess()->getExecutor());
		$this->assertEquals($this->_executor, $this->_manager->getProcess()->getExecutor());
	}
}
