<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Queue
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Queue;

use \PhpTaskDaemon\Task\Job;

/**
 * 
 * The base class implements an example load method. The base class will also 
 * be used when no queue object is available for a certain task.
 */
class BaseClass extends AbstractClass implements InterfaceClass {

    public function load() {
        $queue = array(
            new Job\BaseClass(
                'base-1',
                new Job\Data\BaseClass(
                    array('sleepTime' => 100000)
                )
            ),
            new Job\BaseClass(
                'base-2',
                new Job\Data\BaseClass(
                    array('sleepTime' => 500000)
                )
            ),
            new Job\BaseClass(
                'base-3',
                new Job\Data\BaseClass(
                    array('sleepTime' => 1000000)
                )
            )
        );
        return $queue;
    }

}