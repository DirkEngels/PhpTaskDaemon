<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Ipc
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Daemon
 * @group PhpTaskDaemon-Daemon-Ipc
 */


namespace PhpTaskDaemon\Daemon\Ipc;

class IpcAbstractTest extends \PHPUnit_Framework_TestCase {
    protected $_ipc;

    protected function setUp() {
        $this->_ipc = $this->getMockForAbstractClass(
            '\\PhpTaskDaemon\\Daemon\\Ipc\\IpcAbstract',
            array('ipc-abstract')
        );
    }

    protected function tearDown() {
        unset($this->_ipc);
    }

    public function testConstructor() {
        $this->assertEquals(0, sizeof($this->_ipc->get()));
        $this->assertEquals('a:0:{}', serialize($this->_ipc->get()));
        $this->assertEquals('ipc-abstract', $this->_ipc->getId());
    }

    public function testInitResource() {
        $this->assertTrue($this->_ipc->initResource());
    }

    public function testCleanupResource() {
        $this->assertTrue($this->_ipc->cleanupResource());
    }

    public function testGetKeys() {
        $this->assertEquals(0, sizeof($this->_ipc->getKeys()));
        $this->assertInternalType('array', $this->_ipc->getKeys());
    }


}
