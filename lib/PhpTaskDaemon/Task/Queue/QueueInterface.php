<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Queue
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Queue;

interface QueueInterface {

    public function incrementStatus($status, $count = 1);
    public function decrementQueue($count = 1);
    public function setStatusCount($status = self::STATUS_DONE, $count = 0);
    public function setQueueCount($count = 0);
    public function updateStatus($status, $count = 1, $reset = false);
    public function updateQueue($count = NULL);

    /**
     * Returns an array with jobs.
     * 
     * @return array
     */
    public function load();

}