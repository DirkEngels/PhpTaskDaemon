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
        $manager = \PhpTaskDaemon\Task\Factory::get('Tutorial\\Basics\\Minimal');
        $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Manager\\ManagerAbstract', $manager);
    }

    public function testFactoryGetManagerWithTaskName() {
        $manager = \PhpTaskDaemon\Task\Factory::getManager('Tutorial\\Basics\\Minimal');
        $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Manager\\ManagerAbstract', $manager);
    }

    public function testFactoryGetManagerTimerWithTaskName() {
        $timer = \PhpTaskDaemon\Task\Factory::getManagerTimer('Tutorial\\Basics\\Minimal');
        $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Manager\\Timer\\TimerAbstract', $timer);
    }

    public function testFactoryGetManagerProcessWithTaskName() {
        $process = \PhpTaskDaemon\Task\Factory::getManagerProcess('Tutorial\\Basics\\Minimal');
        $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Manager\\Process\\ProcessAbstract', $process);
    }

    public function testFactoryGetExecutorWithTaskName() {
        $executor = \PhpTaskDaemon\Task\Factory::getExecutor('Tutorial\\Basics\\Minimal');
        $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Executor\\ExecutorAbstract', $executor);
    }

    public function testFactoryGetExecutorStatusWithTaskName() {
        $status = \PhpTaskDaemon\Task\Factory::getExecutorStatus('Tutorial\\Basics\\Minimal');
        $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Executor\\Status\\StatusAbstract', $status);
    }

    public function testFactoryGetQueueWithTaskName() {
        $queue = \PhpTaskDaemon\Task\Factory::getQueue('Tutorial\\Basics\\Minimal');
        $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Queue\\QueueAbstract', $queue);
    }

    public function testFactoryGetQueueStatisticsWithTaskName() {
        $statistics = \PhpTaskDaemon\Task\Factory::getQueueStatistics('Tutorial\\Basics\\Minimal');
        $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Queue\\Statistics\\StatisticsAbstract', $statistics);
    }
}