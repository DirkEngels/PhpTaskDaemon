<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Statistics
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Task\Queue\Statistics;

interface InterfaceClass {
    
    public function get();
    public function setStatusCount($status, $count); 
    public function incrementStatus($status);
    public function setQueueCount($count); 
    public function decrementQueue();
    
}