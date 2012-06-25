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
 * @group PhpTaskDaemon-Daemon-Console
 */


namespace PhpTaskDaemon\Daemon;

use PhpTaskDaemon\Daemon\Tasks;

class ConsoleTest extends \PHPUnit_Framework_TestCase {
    protected $_console;

    protected function setUp() {
        // Stop here and mark this test as incomplete.
//         $this->markTestIncomplete(
//           'This test has not been implemented yet.'
//         );

        $this->_console = new \PhpTaskDaemon\Daemon\Console();
    }

    protected function tearDown() {
    }

    public function testConstructor() {
        $this->assertInstanceOf('Zend_Console_Getopt', $this->_console->getConsoleOpts());
        $this->assertInstanceOf('\\PhpTaskDaemon\\Daemon\\Instance', $this->_console->getInstance());
    }

    public function testConstructorWithInstance() {
        $instance = new \PhpTaskDaemon\Daemon\Instance();
        $this->_console = new \PhpTaskDaemon\Daemon\Console($instance);
        $this->assertInstanceOf('\\PhpTaskDaemon\\Daemon\\Instance', $this->_console->getInstance());
        $this->assertEquals($instance, $this->_console->getInstance());
    }

    public function testsetInstanceUnset() {
        $this->assertInstanceOf('\\PhpTaskDaemon\\Daemon\\Instance', $this->_console->getInstance());
        $this->assertEquals(new \PhpTaskDaemon\Daemon\Instance(), $this->_console->getInstance());

        $tasks = new \PhpTaskDaemon\Daemon\Tasks();
        $tasks->loadManagerByTaskName('Tutorial\\Minimal');
        $instance = new \PhpTaskDaemon\Daemon\Instance();
        $instance->setTasks($tasks);
        $this->_console->setInstance($instance);

        $this->assertInstanceOf('\\PhpTaskDaemon\\Daemon\\Instance', $this->_console->getInstance());
        $this->assertEquals($instance, $this->_console->getInstance());
    }


    public function testSetTasks() {
        $this->assertInstanceOf('\\PhpTaskDaemon\\Daemon\\Tasks', $this->_console->getTasks());
        $this->assertEquals(new \PhpTaskDaemon\Daemon\Tasks(), $this->_console->getTasks());

        $tasks = new Tasks();
        $tasks->addManager(
            new \PhpTaskDaemon\Task\Manager\ManagerDefault()
        );
        $this->assertEquals($this->_console, $this->_console->setTasks($tasks));

        $this->assertInstanceOf('\\PhpTaskDaemon\\Daemon\\Tasks', $this->_console->getTasks());
        $this->assertEquals($tasks, $this->_console->getTasks());
    }


    public function testListTasksNoneFound() {
        $tasks = $this->getMock('\\PhpTaskDaemon\\Daemon\\Tasks');
        $tasks->expects($this->any())
             ->method('scan')
             ->will($this->returnValue(array()));
        $this->_console->setTasks($tasks);

        $this->expectOutputString("List Tasks\n==========\n\nNo tasks found!\n");
        $this->assertNull($this->_console->listTasks());
    }


    public function testListTasksSomeFound() {
        $tasks = $this->getMock('\\PhpTaskDaemon\\Daemon\\Tasks');
        $tasks->expects($this->any())
             ->method('scan')
             ->will($this->returnValue(array('TaskOne', 'TaskTwo')));
        $this->_console->setTasks($tasks);

        $this->expectOutputRegex('/List Tasks/');
        $this->expectOutputRegex('/TaskOne/');
        $this->expectOutputRegex('/TaskTwo/');
        $this->assertNull($this->_console->listTasks());
    }


    public function testSettings() {
        $this->expectOutputRegex('/Daemon Settings/');
        $this->assertNull($this->_console->settings());
    }


    public function testHelp() {
        $this->expectOutputRegex('/Help/');
        $this->assertNull($this->_console->help());
    }


    public function testStart() {
        $instance = $this->getMock('\\PhpTaskDaemon\\Daemon\\Instance', array('setTasks', 'start'));
        $instance->expects($this->once())
             ->method('setTasks')
             ->will($this->returnValue(NULL));
        $instance->expects($this->once())
             ->method('start')
             ->will($this->returnValue(NULL));

        $tasks = $this->getMock('\\PhpTaskDaemon\\Daemon\\Tasks', array('scan', 'loadManagerByTaskName'));
        $tasks->expects($this->once())
             ->method('scan')
             ->will($this->returnValue(array('TaskOne')));
        $tasks->expects($this->once())
             ->method('loadManagerByTaskName')
             ->will($this->returnValue(NULL));

        $this->_console->setInstance($instance);
        $this->_console->setTasks($tasks);

        $this->assertNull($this->_console->start());
    }


    public function testStopNotRunning() {
        $instance = $this->getMock('\\PhpTaskDaemon\\Daemon\\Instance', array('isRunning'));
        $instance->expects($this->once())
             ->method('isRunning')
             ->will($this->returnValue(FALSE));

        $this->_console->setInstance($instance);

        $this->expectOutputString("Daemon is NOT running!!!\n\n");
        $this->assertNull($this->_console->stop());
    }


    public function testStopRunning() {
        $instance = $this->getMock('\\PhpTaskDaemon\\Daemon\\Instance', array('isRunning', 'stop'));
        $instance->expects($this->once())
             ->method('isRunning')
             ->will($this->returnValue(TRUE));
        $instance->expects($this->once())
             ->method('stop')
             ->will($this->returnValue(NULL));

        $this->_console->setInstance($instance);

        $this->expectOutputString("Terminating application  !!!\n\n");
        $this->assertNull($this->_console->stop());
    }


    public function testRestart() {
        $console = $this->getMock('\\PhpTaskDaemon\\Daemon\\Console', array('stop', 'start', '_exit'));
        $console->expects($this->once())
             ->method('stop')
             ->will($this->returnValue(NULL));
        $console->expects($this->once())
             ->method('start')
             ->will($this->returnValue(NULL));
        $console->expects($this->once())
             ->method('_exit')
             ->will($this->returnValue(NULL));
        $this->assertNull($console->restart());
    }

}
