<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager\Trigger
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Task
 * @group PhpTaskDaemon-Task-Manager
 * @group PhpTaskDaemon-Task-Manager-Trigger
 */

namespace PhpTaskDaemon\Task\Manager\Trigger;

class IntervalTest extends \PHPUnit_Framework_Testcase {
    protected $_trigger;

    protected function setUp() {
        $this->_trigger = new \PhpTaskDaemon\Task\Manager\Trigger\Interval();
    }

    protected function tearDown() {
        unset($this->_trigger);
    }

    public function testSetTimeToWait() {
        $this->assertEquals(1, $this->_trigger->getTimeToWait());
        $this->assertEquals($this->_trigger, $this->_trigger->setTimeToWait(5));
        $this->assertEquals(5, $this->_trigger->getTimeToWait());
    }

}
