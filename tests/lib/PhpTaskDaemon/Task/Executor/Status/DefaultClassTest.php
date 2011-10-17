<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Executor
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Task
 * @group PhpTaskDaemon-Task-Executor
 * @group PhpTaskDaemon-Task-Executor-Status
 */

namespace PhpTaskDaemon\Task\Executor\Status;

class DefaultClassTest extends \PHPUnit_Framework_Testcase {
    protected $_status;
    protected $_ipc;

    protected function setUp() {
//        // Stop here and mark this test as incomplete.
//        $this->markTestIncomplete(
//          'This test has not been implemented yet.'
//        );

        $this->_ipc = new \PhpTaskDaemon\Daemon\Ipc\None(\TMP_PATH . '/test-status');
        $this->_status = new \PhpTaskDaemon\Task\Executor\Status\DefaultClass();
        
    }

    protected function tearDown() {
//        $this->_ipc->remove();
//        unset($this->_ipc);
//        $ipc = $this->_status->getIpc();
//        if (is_a($ipc, '\PhpTaskDaemon\Daemon\Ipc\AbstractClass')) {
//            $ipc->remove();
//        }
//        unset($this->_status);
    }

    public function testConstructorNoArguments() {
        $ipcCreated = $this->_status->getIpc();
        $this->assertInstanceOf('\PhpTaskDaemon\Daemon\Ipc\AbstractClass', $ipcCreated);
    }

    public function testConstructorSingleArguments() {
        $this->_status = new \PhpTaskDaemon\Task\Queue\Statistics\DefaultClass($this->_ipc);
        $ipcCreated = $this->_status->getIpc();
        $this->assertInstanceOf('\PhpTaskDaemon\Daemon\Ipc\AbstractClass', $ipcCreated);
    }

    public function testSetIpc() {
        $ipcCreated = $this->_status->getIpc();
        $this->assertInstanceOf('\PhpTaskDaemon\Daemon\Ipc\AbstractClass', $ipcCreated);
        $this->_status->setIpc($this->_ipc);
        $this->assertEquals($this->_ipc, $this->_status->getIpc());
    }

    public function testGetNoArgument() {
        $this->assertInternalType('array', $this->_status->get());
        $this->assertEquals(0, sizeof($this->_status->get()));
    }
}
