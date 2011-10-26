<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\State
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Daemon
 * @group PhpTaskDaemon-Daemon-State
 */


namespace PhpTaskDaemon\Daemon;

class StateTest extends \PHPUnit_Framework_TestCase {
    protected $_tasks;

    protected function setUp() {
        $this->_tasks = new \PhpTaskDaemon\Daemon\Tasks();
    }

    protected function tearDown() {
    }

    public function testNothing() {
        $this->assertTrue(true);
    }

}
