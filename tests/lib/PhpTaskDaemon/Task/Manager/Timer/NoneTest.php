<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Gearman
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

class NoneTest extends \PHPUnit_Framework_TestCase {
    protected $_timer;

    protected function setUp() {
        $this->_timer = new \PhpTaskDaemon\Task\Manager\Timer\None();
    }

    protected function tearDown() {
    }

    public function testGetTimeToWait() {
        $this->assertEquals(0, $this->_timer->getTimeToWait());
    }

}
