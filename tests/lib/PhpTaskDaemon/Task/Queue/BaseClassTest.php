<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Task\Queue
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Task
 * @group PhpTaskDaemon-Task-Queue
 */

namespace PhpTaskDaemon\Task\Queue;

class QueueTest extends \PHPUnit_Framework_Testcase {
	protected $_queue;
	
	protected function setUp() {
	}
	protected function tearDown() {
	}
	
	public function testConstructor() {
		$semaphore = __DIR__ . '/_data/id.shm';
		$this->_queue = new \PhpTaskDaemon\Task\Queue\BaseClass($semaphore);
//		$this->assertEquals(2, sizeof($this->_queue->load()));
	}
	
}
