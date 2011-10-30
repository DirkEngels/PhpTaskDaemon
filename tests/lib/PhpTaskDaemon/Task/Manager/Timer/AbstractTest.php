<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Abstract
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

class AbstractTest extends \PHPUnit_Framework_TestCase {
    protected $_timer;

    protected function setUp() {
        $this->_timer = $this->getMockForAbstractClass(
            '\\PhpTaskDaemon\\Task\\Manager\\Timer\\AbstractClass'
        );
    }

    protected function tearDown() {
    }

    public function testNothing() {
        $this->assertTrue(TRUE);
    }

    public function testSetQueueInvalidArgument() {
        $this->_timer->setQueue('invalid queue object');
        $this->assertEquals(
            new \PhpTaskDaemon\Task\Queue\DefaultClass(),
            $this->_timer->getQueue()
        );
    }
}
