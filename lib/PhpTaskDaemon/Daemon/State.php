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
     * @static
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


    /**
     * This static method returns an array with daemon specific information, 
     * such as the process ID and its children ID's.
     * 
     * @static
     * @return array
     */
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
    
    
    /**
     * This static methods returns an array with all information regarding a
     * queue. A specific queue is identified by its process ID, which can be 
     * retrieved from the daemon state.
     *  
     * @static
     * @param integer $queuePid
     * @return array
     */
    public static function getQueueState($queuePid) {
    	$ipcQueue = Ipc\IpcFactory::get(
    		Ipc\IpcFactory::NAME_QUEUE,
    		$queuePid
    	);
        return $ipcQueue->get();
    }

    
    /**
     * This static methods returns an array with all information regarding an
     * executor. A specific executor is identified by its process ID, which can
     * be retrieved from the daemon state.
     * 
     * @static
     * @param $executorPid
     * @return array
     */
    public static function getExecutorState($executorPid) {
    	$ipcExecutor = Ipc\IpcFactory::get(
    		Ipc\IpcFactory::NAME_EXECUTOR,
    		$executorPid
    	);
        return $ipcExecutor->get();
    }

}
