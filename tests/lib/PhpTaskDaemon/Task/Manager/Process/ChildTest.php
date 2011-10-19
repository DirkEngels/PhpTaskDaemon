<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Child
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Task
 * @group PhpTaskDaemon-Task-Manager
 * @group PhpTaskDaemon-Task-Manager-Process
 */


namespace PhpTaskDaemon\Task\Manager\Process;

class ChildTest extends \PHPUnit_Framework_TestCase {
    protected $_process;

    protected function setUp() {
        $this->_process = new \PhpTaskDaemon\Task\Manager\Process\Child();
    }

    protected function tearDown() {
    }

    public function testNothing() {
        $this->assertTrue(true);
    }

}
