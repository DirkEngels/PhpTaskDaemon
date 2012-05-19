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
 * @group PhpTaskDaemon-Daemon-Ipc-Memcached
 */

namespace PhpTaskDaemon\Daemon\Ipc;

class MemcachedTest extends \PHPUnit_Framework_TestCase {

    protected $_ipc;

    protected function setUp() {
        $this->_ipc = new \PhpTaskDaemon\Daemon\Ipc\Memcached('test');
    }

    public function testTrue() {
        $this->assertTrue( TRUE );
    }

}
