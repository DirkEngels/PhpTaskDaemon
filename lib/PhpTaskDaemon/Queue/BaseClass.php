<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Queue
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Queue;

class BaseClass extends AbstractClass implements InterfaceClass {
	
	public function load() {
		return array(
			'jobId' => rand(0,100),
			'sleepTime' => rand(1,3)
		);
	}
}