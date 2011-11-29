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


namespace PhpTaskDaemon\Task\Tutorial\Basics\Minimal;

require_once(APPLICATION_PATH . '/task/Tutorial/Basics/Minimal/Executor.php');

class ExecutorTest extends \PHPUnit_Framework_TestCase {
    protected $_executor = NULL;

    protected function setUp() {
        $this->_job = new \PhpTaskDaemon\Task\Job\JobDefault(
            'test-job-1',
            new \PhpTaskDaemon\Task\Job\Data\DataDefault(array(
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
            '\\PhpTaskDaemon\\Task\\Job\\JobAbstract', 
            $this->_executor->getJob()
        );
        $this->assertInstanceOf(
            '\\PhpTaskDaemon\\Task\\Job\\Data\\DataAbstract', 
            $this->_executor->getJob()->getInput()
        );
        $this->assertInstanceOf(
            '\\PhpTaskDaemon\\Task\\Job\\Data\\DataAbstract', 
            $this->_executor->getJob()->getOutput()
        );

        $this->assertEquals(2, count($this->_executor->getJob()->getInput()->get()));
        $this->assertEquals(20, $this->_executor->getJob()->getInput()->getVar('sleepTime'));
        $this->assertEquals('data', $this->_executor->getJob()->getInput()->getVar('test'));

        $this->assertEquals(0, count($this->_executor->getJob()->getOutput()->get()));
    }

    /**
     * @depends testConstructor
     */
    public function testRun() {
        $this->_executor->run();

        $this->assertArrayHasKey('returnStatus', $this->_executor->getJob()->getOutput()->get());
        $this->assertEquals(1, count($this->_executor->getJob()->getOutput()->get()));

        $this->assertTrue(in_array(
            $this->_executor->getJob()->getOutput()->getVar('returnStatus'), 
            array('done', 'failed')
        ));
    }

}