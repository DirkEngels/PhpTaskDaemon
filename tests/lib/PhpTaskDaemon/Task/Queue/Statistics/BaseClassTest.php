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
 * @group PhpTaskDaemon-Task-Queue
 * @group PhpTaskDaemon-Task-Queue-Statistics
 */

namespace PhpTaskDaemon\Task\Queue\Statistics;

class BaseClassTest extends \PHPUnit_Framework_Testcase {
	protected $_statistics;
	protected $_sharedMemory;
	
	protected function setUp() {
		$this->_sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory(\TMP_PATH . '/test-statistics');
		$this->_statistics = new \PhpTaskDaemon\Task\Queue\Statistics\BaseClass();
		
	}
	protected function tearDown() {
		$this->_sharedMemory->remove();
		unset($this->_sharedMemory);
		$sharedMemory = $this->_statistics->getSharedMemory();
		if (is_a($sharedMemory, '\PhpTaskDaemon\Daemon\Ipc\SharedMemory')) {
			$sharedMemory->remove();
		}
		unset($this->_statistics);
	}
	
	public function testConstructorNoArguments() {
		$sharedMemoryCreated = $this->_statistics->getSharedMemory();
		$this->assertInstanceOf('\PhpTaskDaemon\Daemon\Ipc\SharedMemory', $sharedMemoryCreated);
	}
	public function testConstructorSingleArguments() {
		$this->_statistics = new \PhpTaskDaemon\Task\Queue\Statistics\BaseClass($this->_sharedMemory);
		$sharedMemoryCreated = $this->_statistics->getSharedMemory();
		$this->assertInstanceOf('\PhpTaskDaemon\Daemon\Ipc\SharedMemory', $sharedMemoryCreated);
	}
	public function testSetSharedMemory() {
		$sharedMemoryCreated = $this->_statistics->getSharedMemory();
		$this->assertInstanceOf('\PhpTaskDaemon\Daemon\Ipc\SharedMemory', $sharedMemoryCreated);
		$this->_statistics->setSharedMemory($this->_sharedMemory);
		$this->assertEquals($this->_sharedMemory, $this->_statistics->getSharedMemory());
	}
	public function testGetAll() {
		$this->assertInternalType('array', $this->_statistics->get());
	}
	public function testGetStatus() {
		$this->assertEquals(0, $this->_statistics->get('otherstatus'));
	}
	public function testSetStatusCountNoArguments() {
		$this->assertTrue($this->_statistics->setStatusCount());
		$this->assertEquals(0, $this->_statistics->get('otherstatus'));
		$this->assertEquals(0, $this->_statistics->get('loaded'));
	}
	public function testSetStatusCountStatusArgument() {
		$this->assertTrue($this->_statistics->setStatusCount('total'));
		$this->assertEquals(0, $this->_statistics->get('total'));
		$this->assertEquals(0, $this->_statistics->get('loaded'));
	}
	public function testSetStatusCountStatusAndCountArgument() {
		$this->assertTrue($this->_statistics->setStatusCount('total', 3));
		$this->assertEquals(3, $this->_statistics->get('total'));
		$this->assertEquals(0, $this->_statistics->get('loaded'));
	}
	public function testIncrementStatusNoArguments() {
		$this->_statistics->setSharedMemory($this->_sharedMemory);
		$this->assertEquals(0, $this->_statistics->get('loaded'));
		$this->assertEquals(0, $this->_statistics->get('done'));
		$this->assertTrue($this->_statistics->incrementStatus());
		$this->assertEquals(1, $this->_statistics->get('done'));
		$this->assertTrue($this->_statistics->incrementStatus());
		$this->assertEquals(2, $this->_statistics->get('done'));
	}
	public function testIncrementStatusStatusArgument() {
		$this->assertEquals(0, $this->_statistics->get('failed'));
		$this->assertTrue($this->_statistics->incrementStatus('failed'));
		$this->assertEquals(1, $this->_statistics->get('failed'));
		$this->assertTrue($this->_statistics->incrementStatus('failed'));
		$this->assertEquals(2, $this->_statistics->get('failed'));
	}
	public function testSetQueueCount() {
		$this->assertEquals(0, $this->_statistics->get('queued'));
		$this->assertEquals(10, $this->_statistics->setQueueCount(10));
		$this->assertEquals(10, $this->_statistics->get('loaded'));
		$this->assertEquals(10, $this->_statistics->get('queued'));
	}
	public function testDecrementQueue() {
		$this->assertEquals(10, $this->_statistics->setQueueCount(10));
		$this->assertEquals(10, $this->_statistics->get('queued'));
		$this->assertTrue($this->_statistics->decrementQueue());
		$this->assertEquals(9, $this->_statistics->get('queued'));
		$this->assertTrue($this->_statistics->decrementQueue());
		$this->assertEquals(8, $this->_statistics->get('queued'));
	}
	
}
