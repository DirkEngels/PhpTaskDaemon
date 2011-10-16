<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager\Trigger
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager\Trigger;

abstract class AbstractClass {

    protected $_queue;


    /**
     * 
     * Returns the current loaded queue array
     * @return \PhpTaskDaemon\Task\Queue\AbstractClass
     */
    public function getQueue() {
        return $this->_queue;
    }


    /**
     * 
     * Sets the current queue to process.
     * @param \PhpTaskDaemon\Task\Queue\AbstractClass $queue
     * @return $this
     */
    public function setQueue($queue) {
        if (!is_a($queue, '\PhpTaskDaemon\Task\Queue\AbstractClass')) {
            $queue = new \PhpTaskDaemon\Task\Queue\DefaultClass();
        }
        $this->_queue = $queue;

        return $this;
    }


    /**
     * Returns the time to wait before to run again.
     */
    public function timeToWait() {
        return 1;
    }

}