<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon;

use PhpTaskDaemon\Daemon\Ipc;

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
     * 
     * @return array
     */
    public static function getState() {
    	$state = self::getDaemonState();

        // Loop Childs
        foreach($state['processes'] as $queuePid) {
            $state['queue-' . $queuePid] = self::getQueueState($queuePid);

            // Executor Status
            foreach ($state[$ipcQueue->getId()]['executors'] as $executorPid) {
				$state['executor-' . $executorPid] = self::getExecutorState($executorPid);
            }
        }

        return $state;
    }


    public static function getDaemonState() {
        $state = array();
        $ipc = Ipc\IpcFactory::get(Ipc\IpcFactory::NAME_DAEMON);
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

        return $state;
    }
    
    
    
    public static function getQueueState($queuePid) {
    	$ipcQueue = self::_getIpc(
    		Ipc\IpcFactory::NAME_QUEUE,
    		$queuePid
    	);
        return $ipcQueue->get();
    }

    
    public static function getExecutorState($executorPid) {
    	$ipcExecutor = self::_getIpc(
    		Ipc\IpcFactory::NAME_EXECUTOR,
    		$executorPid
    	);
        return $ipcExecutor->get();
    }
    
    
    private static function _getIpc($type, $pid = NULL) {
        $ipc = Ipc\IpcFactory::get(
            $type,
            $pid
        );
        echo get_class($ipc);
        return $ipc->get();
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
