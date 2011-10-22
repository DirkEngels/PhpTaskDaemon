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

class AbstractTest extends \PHPUnit_Framework_TestCase {
    protected $_statistics;

    protected function setUp() {
        $this->_statistics = $this->getMockForAbstractClass(
            '\\PhpTaskDaemon\\Task\\Executor\\Statistics\\AbstractClass'
        );
    }

    protected function tearDown() {
    }

    public function testUpdateStatusNoStatusSet() {
        $this->assertFalse($this->_statistics->updateStatus(100));
    }

    public function testUpdateStatusStatusSet() {
        $ipc = $this->getMock('\\PhpTaskDaemon\\Daemon\Ipc\\None', array('getVar'));
        $ipc->expects($this->once())
             ->method('getVar')
             ->will($this->returnValue('value'));
        
        $this->_statistics->setIpc($ipc);
        $this->assertEquals('value', $this->_statistics->get('key'));
    }

}
