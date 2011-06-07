<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Exception
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Task\Exception;

class JobInputInvalid extends \Exception {
    
    public function getMessage() {
        return 'Job input invalid!';
    }
}