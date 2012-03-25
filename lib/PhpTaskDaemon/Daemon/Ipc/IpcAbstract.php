<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Ipc
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon\Ipc;

use PhpTaskDaemon\Daemon\Logger;

/**
 * The abstract class implements storing the variable keys and provides a
 * method for retrieving all registered keys.
 * 
 */
abstract class IpcAbstract {

    /**
     * Unique identifier for the IPC ID.
     * 
     * @var string
     */
    protected $_id = NULL;


    /**
     * This array contains the keys of all registered variables.
     * 
     * @var array
     */
    protected $_keys = array();


    /**
     * Constructs a new unique (id) Ipc object.
     * 
     * @param string $id
     * @return boolean
     */
    public function __construct($id) {
        $this->_id = $id;
        Logger::get()->log('Initialized IPC (' . get_class($this) . ')', \Zend_Log::DEBUG);
        return TRUE;
    }


    /**
     * Initializes the resource, which is needed when forking processes.
     * 
     * @return bool
     */
    public function initResource() {
        return TRUE;
    }


    /**
     * Cleans up any open resources.
     * 
     * @return bool
     */
    public function cleanupResource() {
        return TRUE;
    }


    /**
     * Returns the unique identifier.
     * 
     * @return string
     */
    public function getId() {
        return $this->_id;
    }


    /**
     * Returns the registered keys.
     * 
     * @return array
     */
    public function getKeys() {
        return $this->_keys;
    }


    /**
     * Returns all the registered keys with corresponding values.
     * 
     * @return array
     */
    public function get() {
        $keys = $this->getKeys();
        $data = array();
        foreach($keys as $nr => $key) {
            $data[$key] = $this->getVar($key);
        }
        return $data;
    }


    /**
     * Adds a value to an array key.
     * 
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public function addArrayVar($key, $value) {
        $array = $this->getVar($key);
        if (!is_array($array)) {
            return FALSE;
        }

        if (!in_array($value, $array)) {
            array_push($array, $value);
            $this->setVar($key, $array);
            return TRUE;
        }
        return FALSE;
    }


    /**
     * Removes a value to an array key.
     * 
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public function removeArrayVar($key, $value) {
        $array = $this->getVar($key);
        if (!is_array($array)) {
            return FALSE;
        }

        if (in_array($value, $array)) {
            $array = array_diff($array, array($value));
            $this->setVar($key, $array);
            return TRUE;
        }
        return FALSE;
    }


    /**
     * Removes the ipc data.
     * 
     * @param string $key
     * @return bool
     */
    public function removeVar($key) {
        if (in_array($key, $this->_keys)) {
            unset($this->_keys[$key]);
            return TRUE;
        }
        return FALSE;
    }


    /**
     * Removes all registered keys.
     * 
     * @return boolean
     */
    public function remove() {
        $this->_keys = array();
        return TRUE;
    }

}
