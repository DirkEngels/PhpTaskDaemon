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
 * The Daemon\Ipc\DataBase class is responsible for storing and retrieving
 * inter process communication data from the database.
 */
class DataBase extends AbstractClass implements InterfaceClass {

    /**
     * PDO Object
     * @var PDO
     */
    protected $_pdo;


    /**
     * Getter for the PDO object
     * @return \PDO
     */
    public function getPdo() {
        return $this->_pdo;
    }


    /**
     * Setter of the PDO object
     * @param $pdo \PDO
     */
    public function setJob($pdo) {
        $this->_pdo = $pdo;
    }


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
     * @return NULL
     */
    public function getVar($key) {
        $sql = "SELECT value FROM ipc WHERE key='" . $key . "'";
//        $row = $this->getPdo()->query($sql);
        $row = mysql_fetch_array($this->_queryDataBase($sql));
        return $row['value'];
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
        return $this->_queryDataBase($sql);
    }


    /**
     * 
     * Increments nothing
     * @param string $key
     * @return bool
     */
    public function incrementVar($key) {
        $sql = "UPDATE ipc SET value=value+1 WHERE key='" . $key . "'";
        return $this->_queryDataBase($sql);
    }


    /**
     * 
     * Decrements nothing
     * @param string $key
     * @return bool
     */
    public function decrementVar($key) {
        $sql = "UPDATE ipc SET value=value-1 WHERE key='" . $key . "'";
        return $this->_queryDataBase($sql);
    }


    /**
     * 
     * Removes nothing 
     * @param string $key
     * @return bool
     */
    public function removeVar($key) {
        $sql = "DELETE FROM ipc WHERE key='" . $key . "'";
        return $this->_queryDataBase($sql);
    }


    /**
     * 
     * Removes nothing
     * @return bool
     */
    public function remove() {
        $sql = "DELETE FROM ipc";
        return $this->_queryDataBase($sql);
    }


    /**
     * Executes a single query
     * @param string $query
     */
    protected function _queryDataBase($query) {
        return mysql_query($query);
    }

}
