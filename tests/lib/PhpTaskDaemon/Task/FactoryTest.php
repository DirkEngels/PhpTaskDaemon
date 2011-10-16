<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Task
 * @group PhpTaskDaemon-Task-Factory
 */

namespace PhpTaskDaemon\Task;

class FactoryTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {
    }

    protected function tearDown() {
    }

    public function testFactoryGetWithTaskName() {
        $manager = \PhpTaskDaemon\Task\Factory::get('Tutorial\\Minimal');
        $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Manager\\AbstractClass', $manager);
    }

    public function testFactoryGetManagerWithTaskName() {
        $manager = \PhpTaskDaemon\Task\Factory::getManager('Tutorial\\Minimal');
        $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Manager\\AbstractClass', $manager);
    }

    public function testFactoryGetManagerTriggerWithTaskName() {
        $trigger = \PhpTaskDaemon\Task\Factory::getManagerTrigger('Tutorial\\Minimal');
        $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Manager\\Trigger\\AbstractClass', $trigger);
    }

    public function testFactoryGetManagerProcessWithTaskName() {
        $process = \PhpTaskDaemon\Task\Factory::getManagerProcess('Tutorial\\Minimal');
        $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Manager\\Process\\AbstractClass', $process);
    }

    public function testFactoryGetExecutorWithTaskName() {
        $executor = \PhpTaskDaemon\Task\Factory::getExecutor('Tutorial\\Minimal');
        $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Executor\\AbstractClass', $executor);
    }

    public function testFactoryGetExecutorStatusWithTaskName() {
        $status = \PhpTaskDaemon\Task\Factory::getExecutorStatus('Tutorial\\Minimal');
        $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Executor\\Status\\AbstractClass', $status);
    }

    public function testFactoryGetQueueWithTaskName() {
        $queue = \PhpTaskDaemon\Task\Factory::getQueue('Tutorial\\Minimal');
        $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Queue\\AbstractClass', $queue);
    }

    public function testFactoryGetQueueStatisticsWithTaskName() {
        $statistics = \PhpTaskDaemon\Task\Factory::getQueueStatistics('Tutorial\\Minimal');
        $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Queue\\Statistics\\AbstractClass', $statistics);
    }
}