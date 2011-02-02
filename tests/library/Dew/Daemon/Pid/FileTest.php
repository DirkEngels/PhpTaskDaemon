<?php

class Dew_Daemon_Pid_FileTest extends PHPUnit_Framework_Testcase {
	protected $_file;
	
	protected function setUp() {
	}
	protected function tearDown() {
	}
	
	public function testConstructorNoArguments() {
		$this->_file = new Dew_Daemon_Pid_File();
		$this->assertEquals('/daemon.pid', $this->_file->getFilename());
	}
	public function testConstructorWithFileArgument() {
		$this->_file = new Dew_Daemon_Pid_File('henk.pid');
		$this->assertEquals('henk.pid', $this->_file->getFilename());
	}
	
}