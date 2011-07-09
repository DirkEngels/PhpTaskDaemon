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
 * @group PhpTaskDaemon-Daemon-Ipc-None
 */

namespace PhpTaskDaemon\Daemon\Ipc;

class NoneTest extends \PHPUnit_Framework_Testcase {

    protected $_ipc;

    protected function setUp() {
        $this->_ipc = new \PhpTaskDaemon\Daemon\Ipc\None('test');
    }

    public function testConstructor() {
        $this->assertEquals(0, sizeof($this->_ipc->get()));
        $this->assertEquals('a:0:{}', serialize($this->_ipc->get()));
    }

    public function testGetKeys() {
        $this->assertEquals(0, sizeof($this->_ipc->getKeys()));
        $this->assertInternalType('array', $this->_ipc->getKeys());
    }

    public function testSetVarNew() {
        $this->_ipc->setVar('testvar1', '123456');
        $this->assertEquals(0, sizeof($this->_ipc->get()));
        $this->assertEquals(null, $this->_ipc->getVar('testvar1'));
    }

    public function testSetVarUpdate() {
        $this->_ipc->setVar('testvar1', '123456');
        $this->assertEquals(0, sizeof($this->_ipc->get()));
        $this->assertEquals(null, $this->_ipc->getVar('testvar1'));

        $this->_ipc->setVar('testvar1', '654321');
        $this->assertEquals(0, sizeof($this->_ipc->get()));
        $this->assertEquals(null, $this->_ipc->getVar('testvar1'));
    }

    public function testIncrementVarNew() {
        $this->assertNull($this->_ipc->getVar('testvar1'));

        $this->_ipc->incrementVar('testvar1');
        $this->assertNull($this->_ipc->getVar('testvar1'));
    }

    public function testIncrementVarUpdate() {
        $this->_ipc->setVar('testvar1', '123456');
        $this->assertEquals(0, sizeof($this->_ipc->get()));
        $this->assertEquals(null, $this->_ipc->getVar('testvar1'));

        $this->_ipc->incrementVar('testvar1');
        $this->assertEquals(null, $this->_ipc->getVar('testvar1'));
    }

    public function testDecrementVarNew() {
        $this->assertNull($this->_ipc->getVar('testvar1'));

        $this->_ipc->decrementVar('testvar1');
        $this->assertNull($this->_ipc->getVar('testvar1'));
    }

    public function testDecrementVarUpdate() {
        $this->_ipc->setVar('testvar1', '123456');
        $this->assertEquals(0, sizeof($this->_ipc->get()));
        $this->assertEquals(null, $this->_ipc->getVar('testvar1'));

        $this->_ipc->decrementVar('testvar1');
        $this->assertEquals(null, $this->_ipc->getVar('testvar1'));
    }

    public function testRemoveVar() {
        $this->_ipc->setVar('testvar1', '123456');
        $this->assertEquals(0, sizeof($this->_ipc->get()));
        $this->assertEquals(null, $this->_ipc->getVar('testvar1'));

        $this->_ipc->setVar('testvar2', '654321');
        $this->assertEquals(0, sizeof($this->_ipc->get()));
        $this->assertEquals(null, $this->_ipc->getVar('testvar2'));

        $this->_ipc->removeVar('testvar2');
        $this->assertEquals(0, sizeof($this->_ipc->get()));
        $this->assertEquals(null, $this->_ipc->getVar('testvar1'));
    }

    public function testRemove() {
        $this->_ipc->setVar('testvar1', '123456');
        $this->assertEquals(0, sizeof($this->_ipc->get()));
        $this->assertEquals(null, $this->_ipc->getVar('testvar1'));

        $this->_ipc->setVar('testvar2', '654321');
        $this->assertEquals(0, sizeof($this->_ipc->get()));
        $this->assertEquals(null, $this->_ipc->getVar('testvar2'));

        $this->_ipc->remove();
        $this->assertEquals(0, sizeof($this->_ipc->get()));
        $this->assertEquals(null, $this->_ipc->getVar('testvar1'));
    }

}
