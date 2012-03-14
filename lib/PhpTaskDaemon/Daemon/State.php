<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon;

/**
 * 
 * The State class can be used to read the current state of the daemon from the
 * shared memory. It provides statis methods, so it can easily be integrated
 * within other projects.
 */
class State {

    /**
     * This static method returns an array with the state (statistics + 
     * statuses of active tasks) of all current running tasks.
     * 
     * @return array Current state of all the daemon, queues and tasks.
     */
    public static function getState() {
        $ipcClass = '\\PhpTaskDaemon\\Daemon\\Ipc\\' . Config::get()->getOptionValue('global.ipc');
        if (!class_exists($ipcClass)) {
            $ipcClass = '\\PhpTaskDaemon\\Daemon\\Ipc\\None';
        }
        $ipc = new $ipcClass('phptaskdaemond');

        $state = array();

        $daemonKeys = $ipc->getKeys();

        // Pid
        $state['pid'] = null;
        if (in_array('pid', $daemonKeys)) {
            $state['pid'] = $ipc->getVar('pid');
        }

        // Childs
        if (!in_array('processes', $daemonKeys)) {
            $state['processes'] = array();
        } else {
            $state['processes'] = $ipc->getVar('processes');
        }

        // Loop Childs
        foreach($state['processes'] as $process) {
            // Queue Statistics
            $ipcQueueClass = '\\PhpTaskDaemon\\Daemon\\Ipc\\' . Config::get()->getOptionValue('global.ipc');
            if (!class_exists($ipcQueueClass)) {
                $ipcQueueClass = '\\PhpTaskDaemon\\Daemon\\Ipc\\None';
            }
            $ipcQueue = new $ipcQueueClass('queue-' . $process);
            $state[$ipcQueue->getId()] = $ipcQueue->get();

            // Executor Status
            foreach ($state[$ipcQueue->getId()]['executors'] as $executorPid) {
                $ipcExecutorClass = '\\PhpTaskDaemon\\Daemon\\Ipc\\' . Config::get()->getOptionValue('global.ipc');
                if (!class_exists($ipcExecutorClass)) {
                    $ipcExecutorClass = '\\PhpTaskDaemon\\Daemon\\Ipc\\None';
                }
                $ipcExecutor = new $ipcExecutorClass('executor-' . $executorPid);
                $state[$ipcExecutor->getId()] = $ipcExecutor->get();
            }
        }

        return $state;
    }


    /**
     * This statis method is mainly used by the getState method and returns an
     * array with all statuses of currently running tasks of a particular
     * manager.
     *
     * @param int $childPid
     * @return array
     */
    protected static function _getChildStatus($childPid) {
        $state = array('childPid' => $childPid);
        if (file_exists(TMP_PATH . '/status-' . $childPid . '.shm')) {
            $shm = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory('status-' . $childPid);
            $shmKeys = $shm->getKeys();
            foreach($shm->getKeys() as $key => $value) {
                $state[$key] = $shm->getVar($key);
            }
        }

        return $state;
    }


    /**
     * This statis method is mainly used by the getState method and returns an
     * array with statistics of all currently running tasks of a particular
     * manager.
     *
     * @param int $childPid
     * @return array
     */
    protected static function _getChildStatistics($childPid) {
        $state = array('childPid' => $childPid);
        if (file_exists(TMP_PATH . '/statistics-' . $childPid . '.shm')) {
            $shm = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory('statistics-' . $childPid);
            $shmKeys = $shm->getKeys();
            foreach($shm->getKeys() as $key => $value) {
                $state[$key] = $shm->getVar($key);
            }
        }

        return $state;
    }

}
