<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Queue
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Queue;

/**
 * 
 * The base class encapsulates two methods for updating the current queue count
 * and the statistic information about the executed tasks.
 */
abstract class AbstractClass {

    protected $_statistics;


    /**
     *
     * Optionally a statistics instance can be provided. 
     * @param \PhpTaskDaemon\Task\Queue\Statistics\AbstractClass $statistics
     */
    public function __construct($statistics = null) {
        $this->setStatistics($statistics);
    }


    /**
     *
     * Returns the statistics object 
     * @return \PhpTaskDaemon\Task\Queue\Statistics\AbstractClass
     */
    public function getStatistics() {
        return $this->_statistics;
    }


    /**
     *
     * Sets the statistics object 
     * @param \PhpTaskDaemon\Task\Queue\Statistics\AbstractClass $statistics
     * @return $this
     */
    public function setStatistics($statistics) {
        if (is_a($statistics, "\PhpTaskDaemon\Task\Queue\Statistics\AbstractClass")) {
            $this->_statistics = $statistics;
        }
        return $this;
    }


    /**
     * 
     * Updates the statistic information of executed jobs in the shared memory
     * segment. If no count is given, the current count will be increased by
     * one.
     * @param integer $status
     * @param integer|null $count
     * @return bool
     */
    public function updateStatistics($status, $count = null) {
        if ($this->_statistics != null) {
            if ($count != null) {
                $this->_statistics->setStatusCount($status, $count);
            } else {
                $this->_statistics->incrementStatus($status);
            }
            return true;
        }
        return false;
    }


    /**
     * 
     * Updates the current queue information with the current count. If no
     * count is given, the current count will be decreased by one.
     * @param integer|null $count
     * @return boolean
     */
    public function updateQueue($count = null) {
        if ($this->_statistics != null) {
            if ($count != null) {
                $this->_statistics->setQueueCount($count);
            } else {
                $this->_statistics->decrementQueue();
            }
            return true;
        }
        return false;
    }

}