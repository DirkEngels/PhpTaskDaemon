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
        $this->_queue = new \PhpTaskDaemon\Task\Queue\QueueDefault();
    }
    protected function tearDown() {
    }

    public function testConstructor() {
        $this->assertEquals(3, sizeof($this->_queue->load()));
    }


//    public function testUpdateQueueNoQueueClassSet() {
//        $this->assertFalse($this->_queue->updateQueue('test'));
//    }

//    public function testUpdateQueueCountIsPositive() {
//        $this->assertTrue(
//            $this->_queue->updateQueue(
//                100
//            )
//        );
//    }

    public function testUpdateQueueCountIsZero() {
        $this->assertTrue(
            $this->_queue->updateQueue()
        );
    }
   
    public function testLoad() {
        $result = $this->_queue->load();

        $this->assertInternalType('array', $result);
        $this->assertEquals(3, count($result));
        foreach($result as $item) {
            $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Job\\JobAbstract', $item);
        }
    }
}
