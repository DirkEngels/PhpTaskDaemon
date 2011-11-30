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
 * The Daemon\Ipc\FileSystem class is responsible for storing and retrieving
 * inter process communication data from the database.
 */
class FileSystem extends IpcAbstract implements IpcInterface {

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
     * Returns nothing (NULL) 
     * @param string $key
     * @return mixed
     */
    public function getVar($key) {
    }


    /**
     * 
     * Sets nothing
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function setVar($key, $value) {
        $sql = "REPLACE INTO ipc (key, value) VALUES ('" . $key . "','" . $value . "')";
        return $this->_queryFileSystem($sql);
    }


    /**
     * 
     * Increments nothing
     * @param string $key
     * @return bool
     */
    public function incrementVar($key, $count = 1) {
        $keyFile = $this->_getFileForKey($key);
        $value = (int) file_get_contents($keyFile);
        $value += $count;
        return file_put_contents($keyFile, $value);
    }


    /**
     * 
     * Decrements the value of the key stored in a file.
     * @param string $key
     * @return bool
     */
    public function decrementVar($key, $count = 1) {
        $keyFile = $this->_getFileForKey($key);
        $value = (int) file_get_contents($keyFile);
        if ($value > 0) {
            $value -= $count;
            return file_put_contents($keyFile, $value);
        }
        return false;
    }


    /**
     * 
     * Removes the file from the filesystem 
     * @param string $key
     * @return bool
     */
    public function removeVar($key) {
        $keyFile = $this->_getFileForKey($key);
        if (file_exists($keyFile)) {
            return unlink($keyFile);
        }
        return false;
    }


    /**
     * 
     * Removes nothing
     * @return bool
     */
    public function remove() {
    }

    /**
     * Returns the filename for a specific key
     * @param string $key
     * @output string
     */
    protected function _getFileForKey($key) {
        return $this->getId() . '_' . strtolower($key);
    }

}
