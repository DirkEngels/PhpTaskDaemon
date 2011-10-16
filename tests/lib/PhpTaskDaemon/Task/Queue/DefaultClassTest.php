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

class DefaultClassTest extends \PHPUnit_Framework_Testcase {
    protected $_queue;
    protected $_statistics;
    
    protected function setUp() {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

        $semaphore = __DIR__ . '/_data/constructor.shm';
        $this->_queue = new \PhpTaskDaemon\Task\Queue\DefaultClass($semaphore);
        $sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory(\TMP_PATH . '/test-queue');
        $this->_statistics = new \PhpTaskDaemon\Task\Queue\Statistics\DefaultClass($sharedMemory);
    }
    protected function tearDown() {
//        $sharedMemory = $this->_statistics->getIpc();
//        if (is_a($sharedMemory, '\PhpTaskDaemon\Daemon\Ipc\AbstractClass')) {
//            $sharedMemory->remove();
//        }
//        unset($this->_statistics);
    }

    public function testConstructor() {
        $this->assertEquals(3, sizeof($this->_queue->load()));
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
        $this->assertTrue($this->_queue->updateStatistics('test'));
        $this->assertEquals(1, $this->_queue->getStatistics()->get('test'));
        $this->assertTrue($this->_queue->updateStatistics('test', 10));
        $this->assertEquals(10, $this->_queue->getStatistics()->get('test'));
    }

    public function testUpdateQueueNoStatisticsClassSet() {
        $this->assertNull($this->_queue->getStatistics());
        $this->assertTrue($this->_queue->updateQueue(10));
    }

    public function testUpdateQueueStatisticsClassAlreadySet() {
        $this->assertNull($this->_queue->getStatistics());
        $this->_queue->setStatistics($this->_statistics);
        $this->assertEquals($this->_statistics, $this->_queue->getStatistics());
        $this->assertTrue($this->_queue->updateQueue(10));
        $this->assertEquals(10, $this->_queue->getStatistics()->get('queued'));
        $this->assertTrue($this->_queue->updateQueue());
        $this->assertEquals(9, $this->_queue->getStatistics()->get('queued'));
    }

    public function testLoad() {
        $result = $this->_queue->load();

        $this->assertInternalType('array', $result);
        $this->assertEquals(3, count($result));
        foreach($result as $item) {
            $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Job\\AbstractClass', $item);
        }
    }
}
