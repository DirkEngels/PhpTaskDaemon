<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Queue
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Example\Advanced;

use PhpTaskDaemon\Task\Queue as TaskQueue;
use \PhpTaskDaemon\Task\Job;

/**
 * 
 * The base class implements an example load method. The base class will also 
 * be used when no queue object is available for a certain task.
 */
class Queue extends TaskQueue\AbstractClass implements TaskQueue\InterfaceClass {

    public function load() {
        $queue = array();
        for ($i=0; $i<rand(50,500); $i++) {
            array_push($queue,
                new Job\BaseClass(
                    'advanced-' . $i,
                    new Job\Data\BaseClass(
                        array('sleepTime' => 10000)
                    )
                )
            );
        }
        return $queue;
    }

}