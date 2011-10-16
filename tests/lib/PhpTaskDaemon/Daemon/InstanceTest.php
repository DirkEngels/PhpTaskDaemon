<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Config
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Daemon
 * @group PhpTaskDaemon-Daemon-Instance
 */


namespace PhpTaskDaemon\Daemon\Pid;

class InstanceTest extends \PHPUnit_Framework_TestCase {
    protected $_instance;

    protected function setUp() {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

        $this->_instance = new \PhpTaskDaemon\Daemon\Instance();
    }

    protected function tearDown() {
        unset($this->_instance);
    }

    public function testConstructor() {
        $this->assertInstanceOf('\\PhpTaskDaemon\\Daemon\\Tasks', $this->_instance->getTasks());
    }

    public function testSetTasks() {
        $tasks = new \PhpTaskDaemon\Daemon\Tasks();
        $this->_instance->setTasks($tasks);
        $this->assertEquals($tasks, $this->_instance->getTasks());
    }

}