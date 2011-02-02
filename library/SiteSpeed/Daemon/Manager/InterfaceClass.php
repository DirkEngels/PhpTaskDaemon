<?php

/**
 * @package SiteSpeed
 * @subpackage Daemon\Manager
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace SiteSpeed\Daemon\Manager;

/**
 * 
 * This is the interface for a Daemon_Manager. What more can I say?
 * 
 */
interface InterfaceClass {
	public function executeManager();
}