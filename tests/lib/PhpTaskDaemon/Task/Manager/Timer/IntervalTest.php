<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager\Timer
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Task
 * @group PhpTaskDaemon-Task-Manager
 * @group PhpTaskDaemon-Task-Manager-Timer
 */

namespace PhpTaskDaemon\Task\Manager\Timer;

class IntervalTest extends \PHPUnit_Framework_Testcase {
    protected $_timer;

    protected function setUp() {
        $this->_timer = new \PhpTaskDaemon\Task\Manager\Timer\Interval();
    }

    protected function tearDown() {
        unset($this->_timer);
    }

    public function testSetTimeToWait() {
        $this->assertEquals(1, $this->_timer->getTimeToWait());
        $this->assertEquals($this->_timer, $this->_timer->setTimeToWait(5));
        $this->assertEquals(5, $this->_timer->getTimeToWait());
    }

}
