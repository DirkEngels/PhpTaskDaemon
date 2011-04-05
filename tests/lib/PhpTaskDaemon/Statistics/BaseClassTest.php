<?php

/**
 * @package SiteSpeed
 * @subpackage Daemon\Statistics
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 * @group PhpTaskDaemon_Statistics
 */

namespace PhpTaskDaemon\Statistics;

class BaseClassTest extends \PHPUnit_Framework_Testcase {
	protected $_statistics;
	
	protected function setUp() {
	}
	protected function tearDown() {
	}
	
	public function testNoJobResults() {
		$this->_statistics = new \PhpTaskDaemon\Statistics\BaseClass();
		$this->assertEquals(0, sizeof($this->_statistics->getStatistics()));
	}
	
	public function testAddJobResultDefault() {
        $this->_statistics = new \PhpTaskDaemon\Statistics\BaseClass();
        $this->assertEquals(0, sizeof($this->_statistics->getStatistics()));
        
        $this->_statistics->addJobResult();
        $this->assertEquals(1, sizeof($this->_statistics->getStatistics()));
    }
    
	
	
}