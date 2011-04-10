<?php

/**
 * @package SiteSpeed
 * @subpackage Daemon\Task\Queue\Statistics
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Task
 * @group PhpTaskDaemon-Task-Queue
 * @group PhpTaskDaemon-Task-Queue-Statistics
 */

namespace PhpTaskDaemon\Task\Queue\Statistics;

class BaseClassTest extends \PHPUnit_Framework_Testcase {
	protected $_statistics;
	
	protected function setUp() {
		$sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory(\TMP_PATH . '/test');
		$this->_statistics = new \PhpTaskDaemon\Task\Queue\Statistics\BaseClass($sharedMemory);
		
	}
	protected function tearDown() {
		unset($this->_statistics);
	}
	
	public function testNoJobResults() {
		$state = array();
		$this->assertEquals(0, sizeof($state));
	}
	
}
