<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Executor
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Task\Executor;

/**
 * 
 * The executor base class does notting, but can be used if no executor class
 * is defined.
 */
class DefaultClass extends AbstractClass implements InterfaceClass {

    public function run() {
        return $this->getJob();
    }

}