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
 * @group PhpTaskDaemon-Daemon-Tasks
 */


namespace PhpTaskDaemon\Daemon\Pid;

class TasksTest extends \PHPUnit_Framework_TestCase {
    protected $_tasks;

    protected function setUp() {
        $this->_tasks = new \PhpTaskDaemon\Daemon\Tasks();
    }

    protected function tearDown() {
    }

    public function testConstructor() {
        $this->assertInternalType('array', $this->_tasks->getManagers());
        $this->assertEquals(0, count($this->_tasks->getManagers()));
    }

    public function testAddManagersValidManager() {
        $manager = \PhpTaskDaemon\Task\Factory::get('Tutorial\\Basics\\Minimal');
        $this->_tasks->addManager($manager);
        $this->assertInternalType('array', $this->_tasks->getManagers());
        $this->assertEquals(1, count($this->_tasks->getManagers()));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddManagersInvalidManager() {
        $this->_tasks->addManager('blaat');
        $this->assertInternalType('array', $this->_tasks->getManagers());
        $this->assertEquals(0, count($this->_tasks->getManagers()));
    }

    public function testLoadManagerByTaskName() {
    $this->assertEquals(0, count($this->_tasks->getManagers()));
    $this->assertTrue($this->_tasks->loadManagerByTaskName('Tutorial\\Basics\\Minimal'));
    $this->assertEquals(1, count($this->_tasks->getManagers()));
    }

    public function testLoadManagerByTaskNameInvalidTasks() {
    $this->assertEquals(0, count($this->_tasks->getManagers()));
    $this->assertFalse($this->_tasks->loadManagerByTaskName('Invalid\\TaskName'));
    $this->assertEquals(0, count($this->_tasks->getManagers()));
    }

}
