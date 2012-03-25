<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Ipc
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon\Ipc;

interface IpcInterface {

    /**
     * Returns all the registered keys & valyes.
     * 
     * @return  array All registered keys
     */
    public function getKeys();


    /**
     * Returns a registered IPC key.
     * 
     * @param   string  $key
     * @throws  InvalidArgumentException
     * @return  mixed
     */
    public function getVar($key);

    /**
     * Sets an IPC key.
     * 
     * @param   string  $key
     * @param   mixed   $value
     * @return  boolean
     */
    public function setVar($key, $value);


    /**
     * Increments a numeric key.
     * 
     * @param   string  $key    IPC key
     * @param   integer $count  Increment by $count in stead of 1
     * @return  integer         New value
     */
    public function incrementVar($key, $count = 1);


    /**
     * Decrements a numeric key.
     * 
     * @param   string  $key    IPC key
     * @param   integer $count  Increment by $count in stead of 1
     * @return  integer         New value
     */
    public function decrementVar($key, $count = 1);


    /**
     * Removes a key.
     * 
     * @param   string  $key    IPC key
     * @return  bool
     */
    public function removeVar($key);


    /**
     * Removes all registered keys.
     * 
     * @return bool
     */
    public function remove();

}