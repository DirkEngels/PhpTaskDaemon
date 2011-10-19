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
//        // Stop here and mark this test as incomplete.
//        $this->markTestIncomplete(
//          'This test has not been implemented yet.'
//        );

        $this->_instance = new \PhpTaskDaemon\Daemon\Instance();
    }

    protected function tearDown() {
        unset($this->_instance);
    }

    public function testConstructor() {
        $this->assertInstanceOf('\\PhpTaskDaemon\\Daemon\\Tasks', $this->_instance->getTasks());
    }

    public function testSetPidManager() {
        $this->assertEquals(new \PhpTaskDaemon\Daemon\Pid\Manager(), $this->_instance->getPidManager());

        $pidManager = new \PhpTaskDaemon\Daemon\Pid\Manager();
        $pidManager->addChild(1234);

        $this->_instance->setPidManager($pidManager);
        $this->assertEquals($pidManager, $this->_instance->getPidManager());
    }

    public function testSetPidFile() {
        $this->assertEquals(new \PhpTaskDaemon\Daemon\Pid\File(TMP_PATH . '/phptaskdaemond.pid'), $this->_instance->getPidFile());

        $pidFile = new \PhpTaskDaemon\Daemon\Pid\File('/tmp/test.pid');
        $this->_instance->setPidFile($pidFile);

        $this->assertEquals($pidFile, $this->_instance->getPidFile());
    }

    public function testSetIpc() {
        $this->assertEquals(new \PhpTaskDaemon\Daemon\Ipc\None('phptaskdaemond'), $this->_instance->getIpc());

        $ipc = new \PhpTaskDaemon\Daemon\Ipc\None('ipc-1');
        $this->_instance->setIpc($ipc);

        $this->assertEquals($ipc, $this->_instance->getIpc());
    }

    public function testSetTasks() {
        $this->assertEquals(new \PhpTaskDaemon\Daemon\Tasks(), $this->_instance->getTasks());

        $tasks = new \PhpTaskDaemon\Daemon\Tasks();
        $tasks->addManager(
            new \PhpTaskDaemon\Task\Manager\DefaultClass()
        );
        $this->_instance->setTasks($tasks);
        $this->assertEquals($tasks, $this->_instance->getTasks());
    }

}