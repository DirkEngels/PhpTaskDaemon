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

class StatisticsDefaultTest extends \PHPUnit_Framework_Testcase {
	protected $_statistics;
	protected $_ipc;
	
	protected function setUp() {
//        // Stop here and mark this test as incomplete.
//        $this->markTestIncomplete(
//          'This test has not been implemented yet.'
//        );

		$this->_ipc = new \PhpTaskDaemon\Daemon\Ipc\None(\TMP_PATH . '/test-statistics');
		$this->_statistics = new \PhpTaskDaemon\Task\Queue\Statistics\StatisticsDefault();
		
	}
	protected function tearDown() {
		unset($this->_statistics);
		unset($this->_ipc);
//		$this->_ipc->remove();
//		unset($this->_ipc);
//		$ipc = $this->_statistics->getIpc();
//		if (is_a($ipc, '\PhpTaskDaemon\Daemon\Ipc\IpcAbstract')) {
//			$ipc->remove();
//		}
//		unset($this->_statistics);
	}
	
	public function testConstructorNoArguments() {
		$ipcCreated = $this->_statistics->getIpc();
		$this->assertInstanceOf('\PhpTaskDaemon\Daemon\Ipc\IpcAbstract', $ipcCreated);
	}
	public function testConstructorSingleArguments() {
		$this->_statistics = new \PhpTaskDaemon\Task\Queue\Statistics\StatisticsDefault($this->_ipc);
		$ipcCreated = $this->_statistics->getIpc();
		$this->assertInstanceOf('\PhpTaskDaemon\Daemon\Ipc\IpcAbstract', $ipcCreated);
	}
	public function testSetIpc() {
		$ipcCreated = $this->_statistics->getIpc();
		$this->assertInstanceOf('\PhpTaskDaemon\Daemon\Ipc\IpcAbstract', $ipcCreated);
		$this->_statistics->setIpc($this->_ipc);
		$this->assertEquals($this->_ipc, $this->_statistics->getIpc());
	}
	public function testGetStatus() {
		$this->assertEquals(null, $this->_statistics->get('otherstatus'));
	}
	public function testSetStatusCountNoArguments() {
		$this->assertTrue($this->_statistics->setStatusCount());
		$this->assertEquals(null, $this->_statistics->get('otherstatus'));
		$this->assertEquals(null, $this->_statistics->get('loaded'));
	}
	public function testSetStatusCountStatusArgument() {
		$this->assertTrue($this->_statistics->setStatusCount('total'));
		$this->assertEquals(null, $this->_statistics->get('total'));
		$this->assertEquals(null, $this->_statistics->get('loaded'));
	}
	public function testSetStatusCountStatusAndCountArgument() {
		$this->assertTrue($this->_statistics->setStatusCount('total', 3));
//		$this->assertEquals(3, $this->_statistics->get('total'));
		$this->assertEquals(null, $this->_statistics->get('loaded'));
	}
    /*
	public function testIncrementStatusNoArguments() {
		$this->_statistics->setIpc($this->_ipc);
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
*/

    public function testIncrementStatus() {
        $ipc = $this->getMock('\\PhpTaskDaemon\\Daemon\\Ipc\\None', array('incrementVar'), array('test'));
        $ipc->expects($this->once())
            ->method('incrementVar')
            ->will($this->returnValue(2));
        $this->_statistics->setIpc($ipc);

        $this->assertEquals(2, $this->_statistics->incrementStatus());
    }

    public function testDecrementQueue() {
        $ipc = $this->getMock('\\PhpTaskDaemon\\Daemon\\Ipc\\None', array('decrementVar'), array('test'));
        $ipc->expects($this->once())
            ->method('decrementVar')
            ->will($this->returnValue(2));
        $this->_statistics->setIpc($ipc);

        $this->assertEquals(2, $this->_statistics->decrementQueue());
    }

}
