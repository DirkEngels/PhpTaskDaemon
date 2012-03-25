<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Ipc
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Daemon
 * @group PhpTaskDaemon-Daemon-Ipc
 */


namespace PhpTaskDaemon\Daemon\Ipc;

class FactoryTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {
    }

    protected function tearDown() {
    }

    public function testGetSingleArgument() {
        $ipc = IpcFactory::get(IpcFactory::TYPE_NONE);

        $this->assertInstanceOf('\\PhpTaskDaemon\\Daemon\\Ipc\\None', $ipc);
    }

}
