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

        $state = array();

        $daemonKeys = $ipc->getKeys();

        // Pid
        $state['pid'] = null;
        if (in_array('pid', $daemonKeys)) {
            $state['pid'] = $ipc->getVar('pid');
        }

        // Childs
        if (!in_array('childs', $daemonKeys)) {
            $state['childs'] = array();
        } else {
            $state['childs'] = $ipc->getVar('childs');
        }

        // Loop Childs
        foreach($state['childs'] as $process) {
            $state['process-' . $process] = array('statistics' => array(), 'status' => array());
            $state['process-' . $process]['statistics'] = array(
                'type' => 'Tutorial\\Minimal', 
                'status' => 'Processing jobs',
                'loaded' => 2, 
                'queued' => rand(0,1),
                'done' => 12,
                'failed' => 1,
            );
            $state['process-' . $process]['status']['executor-1234'] = array(
                'pid' => 1234,
                'percentage' => 60,
                'state' => 'Resizing image',
            );
            $state['process-' . $process]['status']['executor-1235'] = array(
                'pid' => 1235,
                'percentage' => 20,
                'state' => 'Downloading image',
            );
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