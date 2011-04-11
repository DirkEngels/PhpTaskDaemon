<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Pid
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Daemon
 * @group PhpTaskDaemon-Daemon-Pid
 * @group PhpTaskDaemon-Daemon-Pid-Manager
 */


namespace PhpTaskDaemon\Daemon\Pid;

class ManagerTest extends \PHPUnit_Framework_Testcase {
	protected $_manager;
	
	protected function setUp() {
	}
	protected function tearDown() {
	}
	
	public function testConstructorNoArguments() {
		$this->_manager = new \PhpTaskDaemon\Daemon\Pid\Manager();
		$this->assertEquals(getmypid(), $this->_manager->getCurrent());
		$this->assertEquals(null, $this->_manager->getParent());
		$this->assertFalse($this->_manager->hasChilds());
		$this->assertEquals(0, sizeof($this->_manager->getChilds()));
	}
	
	public function testConstructorOneArgument() {
		$this->_manager = new \PhpTaskDaemon\Daemon\Pid\Manager(12);
		$this->assertEquals(12, $this->_manager->getCurrent());
		$this->assertEquals(null, $this->_manager->getParent());
		$this->assertFalse($this->_manager->hasChilds());
		$this->assertEquals(0, sizeof($this->_manager->getChilds()));
	}
	public function testConstructorTwoArgument() {
		$this->_manager = new \PhpTaskDaemon\Daemon\Pid\Manager(12, 23);
		$this->assertEquals(12, $this->_manager->getCurrent());
		$this->assertEquals(23, $this->_manager->getParent());
		$this->assertFalse($this->_manager->hasChilds());
		$this->assertEquals(0, sizeof($this->_manager->getChilds()));
	}
	
	public function testAddChildOneItem() {
		$this->_manager = new \PhpTaskDaemon\Daemon\Pid\Manager();
		$this->_manager->addChild(34);
		$this->assertTrue($this->_manager->hasChilds());
		$this->assertEquals(1, sizeof($this->_manager->getChilds()));
		$this->assertEquals(serialize(array(34)), serialize($this->_manager->getChilds()));
	}
	public function testAddChildThreeItems() {
		$this->_manager = new \PhpTaskDaemon\Daemon\Pid\Manager();
		$this->_manager->addChild(34);
		$this->_manager->addChild(56);
		$this->_manager->addChild(78);
		$this->assertTrue($this->_manager->hasChilds());
		$this->assertEquals(3, sizeof($this->_manager->getChilds()));
		$this->assertEquals(serialize(array(34, 56, 78)), serialize($this->_manager->getChilds()));
	}

	public function testRemoveChildExists() {
		$this->_manager = new \PhpTaskDaemon\Daemon\Pid\Manager();
		$this->_manager->addChild(34);
		$this->assertTrue($this->_manager->hasChilds());
		$this->assertEquals(1, sizeof($this->_manager->getChilds()));
		$this->assertEquals(serialize(array(34)), serialize($this->_manager->getChilds()));
		
		$this->assertTrue($this->_manager->removeChild(34));
		$this->assertFalse($this->_manager->hasChilds());
		$this->assertEquals(0, sizeof($this->_manager->getChilds()));
	}
	public function testRemoveChildDoesNotExists() {
		$this->_manager = new \PhpTaskDaemon\Daemon\Pid\Manager();
		$this->_manager->addChild(34);
		$this->assertTrue($this->_manager->hasChilds());
		$this->assertEquals(1, sizeof($this->_manager->getChilds()));
		$this->assertEquals(serialize(array(34)), serialize($this->_manager->getChilds()));
		
		$this->assertFalse($this->_manager->removeChild(35));
		$this->assertTrue($this->_manager->hasChilds());
		$this->assertEquals(1, sizeof($this->_manager->getChilds()));
	}

	public function testForkChildNoParentAndChilds() {
		$this->_manager = new \PhpTaskDaemon\Daemon\Pid\Manager(34);
		$this->_manager->forkChild(36);
		$this->assertEquals(36, $this->_manager->getCurrent());
		$this->assertEquals(34, $this->_manager->getParent());
		$this->assertFalse($this->_manager->hasChilds());
		$this->assertEquals(0, sizeof($this->_manager->getChilds()));
	}
	public function testForkChildWithParent() {
		$this->_manager = new \PhpTaskDaemon\Daemon\Pid\Manager(34, 32);
		$this->_manager->forkChild(36);
		$this->assertEquals(36, $this->_manager->getCurrent());
		$this->assertEquals(34, $this->_manager->getParent());
		$this->assertFalse($this->_manager->hasChilds());
		$this->assertEquals(0, sizeof($this->_manager->getChilds()));
	}
	public function testForkChildWithChilds() {
		$this->_manager = new \PhpTaskDaemon\Daemon\Pid\Manager(34);
		$this->_manager->addChild(36);
		$this->_manager->addChild(37);
		$this->_manager->addChild(38);
		$this->_manager->forkChild(40);
		$this->assertEquals(40, $this->_manager->getCurrent());
		$this->assertEquals(34, $this->_manager->getParent());
		$this->assertFalse($this->_manager->hasChilds());
		$this->assertEquals(0, sizeof($this->_manager->getChilds()));
	}
}
