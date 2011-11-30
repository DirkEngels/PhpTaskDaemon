<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Task\Executor
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Task
 * @group PhpTaskDaemon-Task-Executor
 */


namespace PhpTaskDaemon\Task\Executor;

class ExecutorAbstractTest extends \PHPUnit_Framework_TestCase {
    protected $_executor;

    protected function setUp() {
        $this->_executor = $this->getMockForAbstractClass(
            '\\PhpTaskDaemon\\Task\\Executor\\ExecutorAbstract'
        );
    }

    protected function tearDown() {
    }

    public function testUpdateStatusNoStatusSet() {
        $this->assertFalse($this->_executor->updateStatus(100));
    }

    public function testUpdateStatusStatusSet() {
        $status = $this->getMock('\\PhpTaskDaemon\\Task\\Executor\\Statistics\\StatisticsDefault', array('set'));
        $status->expects($this->once())
             ->method('set')
             ->will($this->returnValue(TRUE));
        
        $this->_executor->setStatus($status);
        $this->assertTrue($this->_executor->updateStatus(100));
    }

}
