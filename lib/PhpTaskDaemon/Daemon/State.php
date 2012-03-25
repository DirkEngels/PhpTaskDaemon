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

    const KEY_PID       = 'pid';
    const KEY_PROCS     = 'processes';
    const KEY_QUEUE     = 'queue';
    const KEY_EXECUTOR  = 'executor';


    /**
     * This static method returns an array with the state (statistics + 
     * statuses of active tasks) of all current running tasks.
     * 
     * @static
     * @return array Current state of all the daemon, queues and tasks.
     */
    public static function getState() {
        $state = self::getDaemonState();

        // Loop Childs
        foreach( $state[ self::KEY_PROCS ] as $queuePid ) {
            $key = self::KEY_QUEUE . '-' . $queuePid;
            $state[ $key ] = self::getStateQueue( $queuePid );
            $executorPids = $state[ $ipcQueue->getId() ][ 'executors' ];

            // Executor Status
            foreach ( $executorPids as $executorPid ) {
                $key = self::KEY_EXECUTOR . '-' . $executorPid;
                $state[ $key ] = self::getStateExecutor( $executorPid );
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
        // Get Daemon IPC Object
        $state = array();
        $ipc = Ipc\IpcFactory::get( Ipc\IpcFactory::NAME_DAEMON );
        $daemonKeys = $ipc->getKeys();

        // Pid
        $state[ self::KEY_PID ] = null;
        if ( in_array( self::KEY_PID, $daemonKeys ) ) {
            $state[ self::KEY_PID ] = $ipc->getVar( self::KEY_PID );
        }

        // Childs
        if ( ! in_array( self::KEY_PROCS, $daemonKeys ) ) {
            $state[ self::KEY_PROCS ] = array();
        } else {
            $state[ self::KEY_PROCS ] = $ipc->getVar( self::KEY_PROCS );
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
    public static function getStateQueue( $queuePid ) {
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
    public static function getStateExecutor( $executorPid ) {
        $ipcExecutor = Ipc\IpcFactory::get(
            Ipc\IpcFactory::NAME_EXECUTOR,
            $executorPid
        );
        return $ipcExecutor->get();
    }

}
