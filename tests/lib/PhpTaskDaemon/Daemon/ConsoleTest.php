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


namespace PhpTaskDaemon\Daemon\Pid;

class ConsoleTest extends \PHPUnit_Extensions_OutputTestCase {
    protected $_console;

    protected function setUp() {
//        // Stop here and mark this test as incomplete.
//        $this->markTestIncomplete(
//          'This test has not been implemented yet.'
//        );

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

    public function testListTasks() {
        $this->expectOutputRegex('/List Tasks/');
        $this->assertNull($this->_console->listTasks());
    }


    public function testSettings() {
        $this->expectOutputRegex('/Daemon Settings/');
        $this->assertNull($this->_console->settings());
    }


    public function testHelp() {
        $this->expectOutputRegex('/Help/');
        $this->assertNull($this->_console->listTasks());
    }

}