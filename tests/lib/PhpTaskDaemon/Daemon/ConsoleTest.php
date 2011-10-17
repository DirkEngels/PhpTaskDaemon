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

class ConsoleTest extends \PHPUnit_Framework_TestCase {
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

    public function testListTasks() {
        $this->assertInternalType('string', $this->_console->listTasks());
    }

    public function testSettings() {
        $this->assertInternalType('string', $this->_console->settings());
    }

}