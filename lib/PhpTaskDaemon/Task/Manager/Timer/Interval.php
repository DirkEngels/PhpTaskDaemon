<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager\Timer
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager\Timer;

class Interval extends TimerAbstract implements TimerInterface {

    protected $_timeToWait = null;


    /**
     * Returns the time to wait.
     * 
     * @output integer
     */
    public function getTimeToWait() {
        if ( is_int( $this->_timeToWait ) || ( $this->_timeToWait > 0 ) ) {
            return $this->_timeToWait;
        }

        return parent::timeToWait();
    }


    /**
     * Sets the time to wait
     * 
     * @param $timeToWait
     * @return $this
     */
    public function setTimeToWait( $timeToWait ) {
        $this->_timeToWait = $timeToWait;
        return $this;
    }

}