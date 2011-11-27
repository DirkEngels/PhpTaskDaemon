<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Ipc
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon\Ipc;

interface IpcInterface {

    public function getKeys();
    public function getVar($key);
    public function setVar($key, $value);
    public function incrementVar($key, $count = 1);
    public function decrementVar($key, $count = 1);
    public function removeVar($key);
    public function remove();

}