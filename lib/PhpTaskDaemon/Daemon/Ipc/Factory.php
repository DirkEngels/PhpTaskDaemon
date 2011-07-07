<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Ipc
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon\Ipc;

/**
 * The factory object creates an IPC instance by reading the configuration file.
 *
 */
class Factory {
    const TYPE_NONE = 'None';
    const TYPE_SHAREDMEMORY = 'SharedMemory';
    const TYPE_DATABASE = 'DataBase';


    /**
     * Instantiates a new Ipc object.
     * @param $ipcType
     * @return \PhpTaskDaemon\Task\Manager\AbstractClass
     */
    public static function get($ipcType, $id) {
        $ipcObject = null;
    	switch($ipcType) {
    		case self::TYPE_SHAREDMEMORY:
    			$ipcObject = new SharedMemory($id);
    			break;
            case self::TYPE_DATABASE:
                $ipcObject = new DataBase($id);
                break;
            case self::TYPE_NONE:
    		default:
                $ipcObject = new None($id);
                break;
    	}
        return $ipcObject;
    }

}