<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Exception
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Daemon\Exception;

class ForkFailed extends \Exception {
	
	public function getMessage() {
		return 'Forking process failed!';
	}
}