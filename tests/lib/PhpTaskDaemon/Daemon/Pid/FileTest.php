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
 * @group PhpTaskDaemon-Daemon-Pid-File
 */

namespace PhpTaskDaemon\Daemon\Pid;

class FileTest extends \PHPUnit_Framework_Testcase {
	protected $_file;
	
	protected function setUp() {
	}
	protected function tearDown() {
	}
	
	public function testConstructorNoArguments() {
		$this->_file = new \PhpTaskDaemon\Daemon\Pid\File();
		$this->assertEquals(\TMP_PATH . '/daemon.pid', $this->_file->getFilename());
	}
	public function testConstructorWithFileArgument() {
		$this->_file = new \PhpTaskDaemon\Daemon\Pid\File(\TMP_PATH . '/henk.pid');
		$this->assertEquals(\TMP_PATH . '/henk.pid', $this->_file->getFilename());
	}
	
	public function testSetFilename() {
		$this->_file = new \PhpTaskDaemon\Daemon\Pid\File();
		$this->assertEquals(\TMP_PATH . '/daemon.pid', $this->_file->getFilename());
		$newFileName = \TMP_PATH . '/setfilename.pid'; 
		$this->_file->setFilename($newFileName);
		$this->assertEquals($newFileName, $this->_file->getFilename());
	}

	public function testReadFileDoesNotExists() {
		$this->_file = new \PhpTaskDaemon\Daemon\Pid\File();
		$this->assertNull($this->_file->read());
	}
		
	public function testReadFileDoesExists() {
		$pidFile = __DIR__ . '/_data/test.pid';
		$this->_file = new \PhpTaskDaemon\Daemon\Pid\File($pidFile);
		$this->assertEquals('1234', $this->_file->read());
	}

	public function testIsRunningAndHasValidPidFile() {
		$pidFile = __DIR__ . '/_data/test.pid';
		$this->_file = new \PhpTaskDaemon\Daemon\Pid\File($pidFile);
		$this->assertTrue($this->_file->isRunning());
	}		
	public function testIsRunningAndHasNoValidPidFile() {
		$this->_file = new \PhpTaskDaemon\Daemon\Pid\File();
		$this->assertFalse($this->_file->isRunning());
	}
	public function testWriteValidPid() {
		$pidFile = __DIR__ . '/_data/tmp.pid';
		$this->_file = new \PhpTaskDaemon\Daemon\Pid\File($pidFile);
		$this->assertTrue($this->_file->write(123456));
		$this->assertTrue(file_exists($this->_file->getFilename()));
		unlink($pidFile);
	}
	public function testWriteNoValidPid() {
		$pidFile = __DIR__ . '/_data/tmp.pid';
		$this->_file = new \PhpTaskDaemon\Daemon\Pid\File($pidFile);
		$this->assertFalse($this->_file->write());
	}

	public function testUnlinkFileExists() {
		$pidFile = __DIR__ . '/_data/tmp.pid';
		$this->_file = new \PhpTaskDaemon\Daemon\Pid\File($pidFile);
		$this->assertTrue($this->_file->write(123456));
		$this->assertTrue(file_exists($this->_file->getFilename()));
		$this->assertTrue($this->_file->unlink());
		$this->assertFalse(file_exists($this->_file->getFilename()));
	}
	public function testUnlinkFileDoesNotExists() {
		$pidFile = __DIR__ . '/_data/tmp.pid';
		$this->_file = new \PhpTaskDaemon\Daemon\Pid\File($pidFile);
		$this->assertFalse(file_exists($this->_file->getFilename()));
		$this->assertFalse($this->_file->unlink());
	}	
}
