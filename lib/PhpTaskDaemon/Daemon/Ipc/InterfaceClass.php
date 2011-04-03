<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Task\Executor
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Daemon\Ipc;

interface InterfaceClass {
	public function get();
	public function getVar($key);
	public function setVar($key, $value);
	public function removeVar($key);
	public function remove();
}