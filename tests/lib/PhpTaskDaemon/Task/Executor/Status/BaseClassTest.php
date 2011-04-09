<?php

/**
 * @package SiteSpeed
 * @subpackage Daemon\Task\Executor\Status
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 * 
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Task
 * @group PhpTaskDaemon-Task-Executor
 * @group PhpTaskDaemon-Task-Executor-Status
 */

namespace PhpTaskDaemon\Task\Executor\Status;

class BaseClassTest extends \PHPUnit_Framework_Testcase {
	protected $_statistics;
	
	protected function setUp() {
	}
	protected function tearDown() {
	}

	public function testNoJobResults() {
//		$state = new \PhpTaskDaemon\Task\Executor\Status\BaseClass::getState();
		$state = array();
		$this->assertEquals(0, sizeof($state));
	}
	
/*	
	public function testAddJobResultDefault() {
		$state = new \PhpTaskDaemon\Task\Executor\Status\BaseClass::getState();
		$this->assertEquals(0, sizeof($state));
        
        	$this->_statistics->addJobResult();
		$state = new \PhpTaskDaemon\Task\Executor\Status\BaseClass::getState();
        	$this->assertEquals(1, sizeof($state));
	}
*/	
}
