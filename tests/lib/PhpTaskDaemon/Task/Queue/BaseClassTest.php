<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Task\Queue
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Task
 * @group PhpTaskDaemon-Task-Queue
 */

namespace PhpTaskDaemon\Task\Queue;

class BaseClassTest extends \PHPUnit_Framework_Testcase {
	protected $_queue;
	protected $_statistics;
	
	protected function setUp() {
		$semaphore = __DIR__ . '/_data/constructor.shm';
		$this->_queue = new \PhpTaskDaemon\Task\Queue\BaseClass($semaphore);
		$sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory(\TMP_PATH . '/test');
		$this->_statistics = new \PhpTaskDaemon\Task\Queue\Statistics\BaseClass($sharedMemory);
	}
	protected function tearDown() {
		if (is_a($this->_statistics, '\PhpTaskDaemon\Task\Queue\Statistics')) {
			unset($this->_statistics);
		}
	}
	
	public function testConstructor() {
		$this->assertEquals(2, sizeof($this->_queue->load()));
	}
	public function testSetStatistics() {
		$this->assertNull($this->_queue->getStatistics());
		$this->_queue->setStatistics($this->_statistics);
		$this->assertEquals($this->_statistics, $this->_queue->getStatistics());
	}
	public function testUpdateStatisticsNoStatisticsClassSet() {
		$this->assertNull($this->_queue->getStatistics());
		$this->assertFalse($this->_queue->updateStatistics('test'));
	}
	public function testUpdateStatisticsClassAlreadySet() {
		$this->assertNull($this->_queue->getStatistics());
		$this->_queue->setStatistics($this->_statistics);
		$this->assertEquals($this->_statistics, $this->_queue->getStatistics());
//		$this->assertTrue($this->_queue->updateStatistics('test'));
	}

	
}
