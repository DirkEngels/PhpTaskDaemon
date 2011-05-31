<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager\Trigger
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager\Trigger;

class Cron extends Interval {

	public function getTimeToWait() {
        return $this->_timeToWait;
    }
    
    /**
     * Sets the time to wait
     * @param $timeToWait
     */
    public function setTimeToWait($timeToWait) {
        $this->_timeToWait = $timeToWait;
    }

}