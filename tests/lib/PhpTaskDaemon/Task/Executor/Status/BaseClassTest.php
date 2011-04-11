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
 * @group PhpTaskDaemon-Task-Executor-Status
 */

namespace PhpTaskDaemon\Task\Executor\Status;

class BaseClassTest extends \PHPUnit_Framework_Testcase {
	protected $_status;
	protected $_sharedMemory;
	
	protected function setUp() {
		$this->_sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory(\TMP_PATH . '/test-status');
		$this->_status = new \PhpTaskDaemon\Task\Executor\Status\BaseClass();
		
	}
	protected function tearDown() {
		$this->_sharedMemory->remove();
		unset($this->_sharedMemory);
		$sharedMemory = $this->_status->getSharedMemory();
		if (is_a($sharedMemory, '\PhpTaskDaemon\Daemon\Ipc\SharedMemory')) {
			$sharedMemory->remove();
		}
		unset($this->_status);
	}

	public function testConstructorNoArguments() {
		$sharedMemoryCreated = $this->_status->getSharedMemory();
		$this->assertType('\PhpTaskDaemon\Daemon\Ipc\SharedMemory', $sharedMemoryCreated);
	}
	public function testConstructorSingleArguments() {
		$this->_status = new \PhpTaskDaemon\Task\Queue\Statistics\BaseClass($this->_sharedMemory);
		$sharedMemoryCreated = $this->_status->getSharedMemory();
		$this->assertType('\PhpTaskDaemon\Daemon\Ipc\SharedMemory', $sharedMemoryCreated);
	}
	public function testSetSharedMemory() {
		$sharedMemoryCreated = $this->_status->getSharedMemory();
		$this->assertType('\PhpTaskDaemon\Daemon\Ipc\SharedMemory', $sharedMemoryCreated);
		$this->_status->setSharedMemory($this->_sharedMemory);
		$this->assertEquals($this->_sharedMemory, $this->_status->getSharedMemory());
	}
	public function testGetNoArgument() {
		$this->assertType('array', $this->_status->get());
		$this->assertEquals(0, sizeof($this->_status->get()));
	}
}
