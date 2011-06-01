<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Exception
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Exception;

class DaemonRuntimeError extends \Exception {
    
    public function getMessage() {
        return 'Unknown runtime error!';
    }
}