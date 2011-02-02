<?php

/**
 * @package SiteSpeed
 * @subpackage Daemon\Task
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace SiteSpeed\Daemon\Task;

interface InterfaceClass {
	
	public function executeTask();
	public function loadTasks();
	
}