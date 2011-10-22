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
     * 
     * This static method returns an array with the state (statistics + 
     * statuses of active tasks) of all current running tasks.
     * @return array
     */
    public static function getState() {
        $ipcClass = '\\PhpTaskDaemon\\Daemon\\Ipc\\' . Config::get()->getOptionValue('global.ipc');
        if (!class_exists($ipcClass)) {
            $ipcClass = '\\PhpTaskDaemon\\Daemon\\Ipc\\None';
        }
        $ipc = new $ipcClass('phptaskdaemond');

        $state = $ipc->getKeys();

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