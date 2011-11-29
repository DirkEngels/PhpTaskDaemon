<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Same
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Task
 * @group PhpTaskDaemon-Task-Manager
 * @group PhpTaskDaemon-Task-Manager-Process
 */


namespace PhpTaskDaemon\Task\Manager\Process;

class SameTest extends \PHPUnit_Framework_TestCase {
    protected $_process;

    protected function setUp() {
        $this->_process = new \PhpTaskDaemon\Task\Manager\Process\Same();
    }

    protected function tearDown() {
    }

    public function testNothing() {
        $this->assertTrue(true);
    }

    public function testRun() {
        $process = $this->getMock('\\PhpTaskDaemon\\Task\\Manager\\Process\\Same', array('_processTask'));
        $process->expects($this->exactly(2))
            ->method('_processTask')
            ->will($this->returnValue(NULL));

        $process->setJobs(
	    $jobs = array(
                new \PhpTaskDaemon\Task\Job\JobDefault(),
                new \PhpTaskDaemon\Task\Job\JobDefault(),
            )
        );

        $this->assertNull($process->run());
    }
}
