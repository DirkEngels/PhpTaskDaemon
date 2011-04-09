<?php

/**
 * @package SiteSpeed
 * @subpackage Daemon\Pid
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 * @group PhpTaskDaemon_Pid
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
		$this->_file = new \PhpTaskDaemon\Daemon\Pid\File('henk.pid');
		$this->assertEquals(\TMP_PATH . '/henk.pid', $this->_file->getFilename());
	}
	
}