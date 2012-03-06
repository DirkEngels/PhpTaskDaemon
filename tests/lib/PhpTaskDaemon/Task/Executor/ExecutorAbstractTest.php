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

    public function testNothing() {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}
