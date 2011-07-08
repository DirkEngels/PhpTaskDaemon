<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Executor\Status
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Task\Executor\Status;

interface InterfaceClass {

    public function get();
    public function set($status);

}