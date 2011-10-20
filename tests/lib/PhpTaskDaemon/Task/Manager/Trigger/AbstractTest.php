<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Abstract
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Task
 * @group PhpTaskDaemon-Task-Manager
 * @group PhpTaskDaemon-Task-Manager-Trigger
 */


namespace PhpTaskDaemon\Task\Manager\Trigger;

class AbstractTest extends \PHPUnit_Framework_TestCase {
    protected $_trigger;

    protected function setUp() {
        $this->_trigger = $this->getMockForAbstractClass(
            '\\PhpTaskDaemon\\Task\\Manager\\Trigger\\AbstractClass'
        );
    }

    protected function tearDown() {
    }

}
