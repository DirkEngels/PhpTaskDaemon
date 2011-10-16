<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Task\Tutorial\Minimal
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Task
 * @group PhpTaskDaemon-Task-Tutorial
 * @group PhpTaskDaemon-Task-Tutorial-Minimal
 */


namespace PhpTaskDaemon\Task\Tutorial\Advanced;

require_once(APPLICATION_PATH . '/task/Tutorial/Advanced/Executor.php');

class ExecutorTest extends \PHPUnit_Framework_TestCase {
    protected $_executor = NULL;

    protected function setUp() {
        $this->_job = new \PhpTaskDaemon\Task\Job\DefaultClass(
            'test-job-1',
            new \PhpTaskDaemon\Task\Job\Data\DefaultClass(array(
                'sleepTime' => 20,
                'test' => 'data'
            ))
        );
        $this->_executor = new Executor();
        $this->_executor->setJob($this->_job);
    }

    protected function tearDown() {
    }

    public function testConstructor() {
        $this->assertInstanceOf(
            '\\PhpTaskDaemon\\Task\\Job\\AbstractClass', 
            $this->_executor->getJob()
        );
    }

    /**
     * @depends testConstructor
     */
    public function testRun() {
        $this->_executor->run();

        $this->assertArrayHasKey('returnStatus', $this->_executor->getJob()->getOutput()->get());
        $this->assertEquals(2, count($this->_executor->getJob()->getOutput()->get()));
        
        $this->assertGreaterThanOrEqual(1, $this->_executor->getJob()->getOutput()->getVar('waitTime'));
        $this->assertLessThanOrEqual(5, $this->_executor->getJob()->getOutput()->getVar('waitTime'));
    }

}