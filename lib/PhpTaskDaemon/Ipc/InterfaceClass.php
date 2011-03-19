<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Task\Executor
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Ipc;

interface InterfaceClass {
	protected $_keys = array();

	public function get();
	public function getVar();
	public function setVar();
	public function removeVar();
	public function remove();
}