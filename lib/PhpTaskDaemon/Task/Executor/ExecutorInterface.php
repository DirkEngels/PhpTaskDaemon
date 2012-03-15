
<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Executor
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Task\Executor;

interface ExecutorInterface {

    public function getJob();
    public function setJob(\PhpTaskDaemon\Task\Job\JobAbstract $job);
    public function getIpc();
    public function setIpc(\PhpTaskDaemon\Daemon\Ipc\IpcAbstract $ipc);
    public function resetIpc();
    public function resetPid();
    public function getStatus($key);
    public function setStatus($percentage, $message);
    public function run();

}