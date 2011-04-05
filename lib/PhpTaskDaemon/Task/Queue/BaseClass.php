<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Queue
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Task\Queue;

class BaseClass extends AbstractClass implements InterfaceClass {
	
	public function load() {
		$queue = array(
			new \PhpTaskDaemon\Task\Job\BaseClass(
				'base-' . rand(0,100),
				array(
					'sleepTime' => rand(100000, 500000)
				)
			)
		);
		return $queue;
	}
}