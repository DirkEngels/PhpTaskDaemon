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

class QueueDefaultTest extends \PHPUnit_Framework_Testcase {
    protected $_queue;
    protected $_statistics;

    protected function setUp() {
//        // Stop here and mark this test as incomplete.
//        $this->markTestIncomplete(
//          'This test has not been implemented yet.'
//        );

        $semaphore = __DIR__ . '/_data/constructor.shm';
        $this->_queue = new \PhpTaskDaemon\Task\Queue\QueueDefault($semaphore);
        $ipc = new \PhpTaskDaemon\Daemon\Ipc\None(\TMP_PATH . '/test-queue');
        $this->_statistics = new \PhpTaskDaemon\Task\Queue\Statistics\StatisticsDefault($ipc);
    }
    protected function tearDown() {
//        $ipc = $this->_statistics->getIpc();
//        if (is_a($ipc, '\PhpTaskDaemon\Daemon\Ipc\IpcAbstract')) {
//            $ipc->remove();
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

    public function testUpdateStatisticsCountIsPositive() {
        $statistics = $this->getMock(
            '\\PhpTaskDaemon\\Task\\Queue\\Statistics\StatisticsDefault', 
            array('setStatusCount')
        );
        $statistics->expects($this->once())
            ->method('setStatusCount')
            ->will($this->returnValue(NULL));
        $this->_queue->setStatistics($statistics);

        $this->assertTrue(
            $this->_queue->updateStatistics(
                \PhpTaskDaemon\Task\Queue\Statistics\StatisticsAbstract::STATUS_DONE,
                100,
                true
            )
        );
    }

    public function testUpdateStatisticsCountIsZero() {
        $statistics = $this->getMock(
            '\\PhpTaskDaemon\\Task\\Queue\\Statistics\StatisticsDefault', 
            array('incrementStatus')
        );
        $statistics->expects($this->once())
            ->method('incrementStatus')
            ->will($this->returnValue(NULL));
        $this->_queue->setStatistics($statistics);

        $this->assertTrue(
            $this->_queue->updateStatistics(
                \PhpTaskDaemon\Task\Queue\Statistics\StatisticsAbstract::STATUS_DONE
            )
        );
    }

    public function testUpdateQueueNoQueueClassSet() {
        $this->assertNull($this->_queue->getStatistics());
        $this->assertFalse($this->_queue->updateQueue('test'));
    }

    public function testUpdateQueueCountIsPositive() {
        $statistics = $this->getMock(
            '\\PhpTaskDaemon\\Task\\Queue\\Statistics\\StatisticsDefault', 
            array('setQueueCount')
        );
        $statistics->expects($this->once())
            ->method('setQueueCount')
            ->will($this->returnValue(NULL));
        $this->_queue->setStatistics($statistics);

        $this->assertTrue(
            $this->_queue->updateQueue(
                100
            )
        );
    }

    public function testUpdateQueueCountIsZero() {
        $statistics = $this->getMock(
            '\\PhpTaskDaemon\\Task\\Queue\\Statistics\\StatisticsDefault', 
            array('decrementQueue')
        );
        $statistics->expects($this->once())
            ->method('decrementQueue')
            ->will($this->returnValue(NULL));
        $this->_queue->setStatistics($statistics);

        $this->assertTrue(
            $this->_queue->updateQueue()
        );
    }
    
    /*
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
    */

    public function testLoad() {
        $result = $this->_queue->load();

        $this->assertInternalType('array', $result);
        $this->assertEquals(3, count($result));
        foreach($result as $item) {
            $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Job\\JobAbstract', $item);
        }
    }
}
