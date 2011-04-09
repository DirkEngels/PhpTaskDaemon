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
 * @group PhpTaskDaemon-Daemon-Ipc-SharedMemory
 */

namespace PhpTaskDaemon\Daemon\Ipc;

class SharedMemoryTest extends \PHPUnit_Framework_Testcase {
	protected $_sharedMemory;
	
	protected function setUp() {
	}
	protected function tearDown() {
	}
	
	public function testConstructor() {
		$semaphore = __DIR__ . '/_data/id.shm';
		$this->_sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory($semaphore);
		$this->assertEquals(0, sizeof($this->_sharedMemory->get()));
	}
}
