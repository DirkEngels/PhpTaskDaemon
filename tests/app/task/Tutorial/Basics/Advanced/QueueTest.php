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
 * @group PhpTaskDaemon-Task-Tutorial-Advanced
 */


namespace PhpTaskDaemon\Task\Tutorial\Basics\Advanced;

require_once(APPLICATION_PATH . '/task/Tutorial/Basics/Advanced/Queue.php');

class QueueTest extends \PHPUnit_Framework_TestCase {
    protected $_queue = NULL;

    protected function setUp() {
        $this->_queue = new Queue();
    }

    protected function tearDown() {
    }


    public function testLoad() {
        $result = $this->_queue->load();

        $this->assertInternalType('array', $result);
        $this->assertGreaterThanOrEqual(50, count($result));
        $this->assertLessThanOrEqual(500, count($result));
        foreach($result as $item) {
            $this->assertInstanceOf('\\PhpTaskDaemon\\Task\\Job\\JobAbstract', $item);
        }
    }

}
