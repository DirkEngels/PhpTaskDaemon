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
 * 
 * The Daemon\Ipc\None class is responsible for storing and retrieving nothing.
 */
class None extends AbstractClass implements InterfaceClass {

    public function __construct($id = 'none') {
        parent::__construct($id);
    }

    /**
     * 
     * Returns nothing (NULL) 
     * @param string $key
     * @return NULL
     */
    public function getVar($key) {
        return NULL;
    }


    /**
     * 
     * Sets nothing
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function setVar($key, $value) {
        return TRUE;
    }


    /**
     * 
     * Increments nothing
     * @param string $key
     * @return bool
     */
    public function incrementVar($key, $count = 1) {
        return TRUE;
    }


    /**
     * 
     * Decrements nothing
     * @param string $key
     * @return bool
     */
    public function decrementVar($key, $count = 1) {
        return TRUE;
    }


    /**
     * 
     * Removes nothing 
     * @param string $key
     * @return bool
     */
    public function removeVar($key) {
        return TRUE;
    }


    /**
     * 
     * Removes nothing
     * @return bool
     */
    public function remove() {
        return TRUE;
    }

}
