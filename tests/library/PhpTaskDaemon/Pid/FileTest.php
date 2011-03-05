<?php

namespace PhpTaskDaemon\Pid;

class FileTest extends PHPUnit_Framework_Testcase {
	protected $_file;
	
	protected function setUp() {
	}
	protected function tearDown() {
	}
	
	public function testConstructorNoArguments() {
		$this->_file = new \PhpTaskDaemon\Pid\File();
		$this->assertEquals('/daemon.pid', $this->_file->getFilename());
	}
	public function testConstructorWithFileArgument() {
		$this->_file = new \PhpTaskDaemon\Pid\File('henk.pid');
		$this->assertEquals('henk.pid', $this->_file->getFilename());
	}
	
}