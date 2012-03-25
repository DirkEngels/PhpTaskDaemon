<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Ipc
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon\Ipc;
use PhpTaskDaemon\Daemon\Config;
use PhpTaskDaemon\Daemon\Logger;

/**
 * The factory object creates an IPC instance by reading the configuration file.
 *
 */
class IpcFactory {

    const TYPE_NONE = 'None';
    const TYPE_SHAREDMEMORY = 'SharedMemory';
    const TYPE_FILESYSTEM = 'FileSystem';
    const TYPE_DATABASE = 'DataBase';

    const NAME_DAEMON = 'phptaskdaemond';
    const NAME_QUEUE = 'queue';
    const NAME_EXECUTOR = 'executor';


    /**
     * Instantiates a new Ipc object.
     * 
     * @param $ipcType
     * @return \PhpTaskDaemon\Task\Manager\ManagerAbstract
     */
    public static function get($type = self::NAME_DAEMON, $id = NULL, $taskName = NULL) {
        // Set IPC ID
        $ipcId = $type;
    	if ( is_null($id) ) {
            $id = getmypid();
        }

        // Append process ID for queues and executors
        if ( $type != self::NAME_DAEMON) {
        	$ipcId .= '-' . $id;
        }

        $ipcType = Config::get()->getOptionValue('global.ipc', $taskName);
        switch($ipcType) {
            case self::TYPE_DATABASE:
                $ipcObject = new DataBase($ipcId);
                break;
            case self::TYPE_FILESYSTEM:
                $ipcObject = new FileSystem($ipcId);
                break;
            case self::TYPE_SHAREDMEMORY:
                $ipcObject = new SharedMemory($ipcId);
                break;
            case self::TYPE_NONE:
            default:
                $ipcType = 'None';
                $ipcObject = new None($ipcId);
                break;
        }

        Logger::log('Create new IPC object (' . $ipcType . '): ' . $ipcId, \Zend_Log::DEBUG);
        return $ipcObject;
    }

}
