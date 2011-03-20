<?php

/**
 * @package SiteSpeed
 * @subpackage Daemon\Pid
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 * @group PhpTaskDaemon_Pid
 */


namespace PhpTaskDaemon\Pid;

class ManagerTest extends \PHPUnit_Framework_Testcase {
	protected $_manager;
	
	protected function setUp() {
	}
	protected function tearDown() {
	}
	
	public function testConstructorNoArguments() {
		$this->_manager = new \PhpTaskDaemon\Pid\Manager();
		$this->assertEquals(null, $this->_manager->getCurrent());
		$this->assertEquals(null, $this->_manager->getParent());
		$this->assertEquals(false, $this->_manager->hasChilds());
		$this->assertEquals(0, sizeof($this->_manager->getChilds()));
	}
	
	public function testConstructorOneArgument() {
		$this->_manager = new \PhpTaskDaemon\Pid\Manager(12);
		$this->assertEquals(12, $this->_manager->getCurrent());
		$this->assertEquals(null, $this->_manager->getParent());
		$this->assertEquals(false, $this->_manager->hasChilds());
		$this->assertEquals(0, sizeof($this->_manager->getChilds()));
	}
	public function testConstructorTwoArgument() {
		$this->_manager = new \PhpTaskDaemon\Pid\Manager(12, 23);
		$this->assertEquals(12, $this->_manager->getCurrent());
		$this->assertEquals(23, $this->_manager->getParent());
		$this->assertEquals(false, $this->_manager->hasChilds());
		$this->assertEquals(0, sizeof($this->_manager->getChilds()));
	}
	
	public function testAddChildOneItem() {
		$this->_manager = new \PhpTaskDaemon\Pid\Manager();
		$this->_manager->addChild(34);
		$this->assertEquals(true, $this->_manager->hasChilds());
		$this->assertEquals(1, sizeof($this->_manager->getChilds()));
		$this->assertEquals(serialize(array(34)), serialize($this->_manager->getChilds()));
	}
	public function testAddChildThreeItems() {
		$this->_manager = new \PhpTaskDaemon\Pid\Manager();
		$this->_manager->addChild(34);
		$this->_manager->addChild(56);
		$this->_manager->addChild(78);
		$this->assertEquals(true, $this->_manager->hasChilds());
		$this->assertEquals(3, sizeof($this->_manager->getChilds()));
		$this->assertEquals(serialize(array(34, 56, 78)), serialize($this->_manager->getChilds()));
	}
	
}