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
    
    /**
     * 
     * Returns an empty array
     * @return array
     */
    public function getKeys() {
        return array();
    }
    
    /**
     * 
     * Returns nothing (null) 
     * @param string $key
     * @return null
     */
    public function getVar($key) {
        return null;
    }

    /**
     * 
     * Sets nothing
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function setVar($key, $value) {
    	return true;
    }

    /**
     * 
     * Increments nothing
     * @param string $key
     * @return bool
     */
    public function incrementVar($key) {
    	return true;
    }

    /**
     * 
     * Decrements nothing
     * @param string $key
     * @return bool
     */
    public function decrementVar($key) {
    	return true;
    }

    /**
     * 
     * Removes nothing 
     * @param string $key
     * @return bool
     */
    public function removeVar($key) {
    	return true;
    }
    
    /**
     * 
     * Removes nothing
     * @return bool
     */
    public function remove() {
    	return true;
    }
}