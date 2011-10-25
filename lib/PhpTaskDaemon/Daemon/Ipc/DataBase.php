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
 * 
 * The Daemon\Ipc\DataBase class is responsible for storing and retrieving
 * inter process communication data from the database.
 */
class DataBase extends AbstractClass implements InterfaceClass {

    /**
     * PDO Object
     * @var \PDO
     */
    protected $_pdo;

    /**
     * PDO Statement Object
     * @var \PDOStatement
     */
    protected $_stmt;


    public function __construct($id) {
        parent::__construct($id);
        $this->_dbSetup();
    }


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
        $sql = "SELECT name FROM ipc WHERE ipcId=:ipcId";
        $params = array(
            'ipcId' => $this->_id,
        );
        $this->_dbStatement($sql, $params);
        return $this->_stmt->fetchAll();
    }


    /**
     * 
     * Returns nothing (NULL) 
     * @param string $name
     * @return NULL
     */
    public function getVar($name) {
        $sql = "SELECT value FROM ipc WHERE ipcId=:ipcId AND name=:name";
        $params = array(
            'ipcId' => $this->_id,
            'name' => $name,
        );
        $this->_dbStatement($sql, $params);
//        if ($this->_stmt->rowCount() == 0) {
//            throw new \Exception('Value for key (' . $name . ') does not exists (ipcId: ' . $this->_id . ')', \Zend_Log::ERR);
//        }
        return unserialize($this->_stmt->fetchColumn());
    }


    /**
     * 
     * Sets nothing
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function setVar($name, $value) {

        $sql = "REPLACE INTO ipc (ipcId, name, value) VALUES (:ipcId, :name, :value)";
        $params = array(
            'ipcId' => $this->_id,
            'name' => $name,
            'value' => serialize($value),
        );
        $this->_dbStatement($sql, $params);
    }


    /**
     * 
     * Increments nothing
     * @param string $name
     * @return bool
     */
    public function incrementVar($name, $count = 1) {
        $this->_pdo->beginTransaction();

        $value = $this->getVar($name);
        if (!isset($value)) {
            $value = 0;
        }
        $value += $count;

        $sql = "UPDATE ipc SET value=:value WHERE ipcId=:ipcId AND name=:name";
        $params = array(
            'ipcId' => $this->_id,
            'name' => $name,
            'value' => serialize($value),
        );
        $this->_dbStatement($sql, $params);

        return $this->getPdo()->commit();
    }


    /**
     * 
     * Decrements nothing
     * @param string $name
     * @return bool
     */
    public function decrementVar($name, $count = 1) {
        $this->_pdo->beginTransaction();

        $value = $this->getVar($name);
        if (!isset($value)) {
            $value = 0;
        }
        $value -= $count;

        $sql = "UPDATE ipc SET value=:value WHERE ipcId=:ipcId AND name=:name";
        $params = array(
            'ipcId' => $this->_id,
            'name' => $name,
            'value' => serialize($value),
        );
        $this->_dbStatement($sql, $params);

        return $this->getPdo()->commit();
    }


    /**
     * 
     * Removes nothing 
     * @param string $name
     * @return bool
     */
    public function removeVar($name) {
        $sql = "DELETE FROM ipc WHERE ipcId=:ipcId AND name=:name";
        $params = array(
            'ipcId' => $this->_id,
            'name' => $name,
        );
        return $this->_dbStatement($sql, $params);
    }


    /**
     * 
     * Removes nothing
     * @return bool
     */
    public function remove() {
        $sql = "DELETE FROM ipc WHERE ipdKey=:ipcId";
        $params = array(
            'ipcId' => $this->_id,
        );
        return $this->_dbStatement($sql, $params);
    }


    protected function _dbSetup() {
        // Try loading PDO from config
        try {
            $dbname = Config::get()->getOptionValue('db.params.dbname');
            switch(Config::get()->getOptionValue('db.adapter')) {
                case 'mysql':
                case 'pgsql':
                    $hostname = Config::get()->getOptionValue('db.params.hostname');
                    $username = Config::get()->getOptionValue('db.params.username');
                    $password = Config::get()->getOptionValue('db.params.password');
                    $this->_pdo = new \PDO(
                        Config::get()->getOptionValue('db.adapter') . ':host=' . $hostname . ';dbname=' . $dbname, 
                        $username, 
                        $password
                    );
                    break;
                case 'sqlite':
                    $this->_pdo = new \PDO('sqlite:' . \TMP_PATH . '/' . $id . '.sdb');
                    break;
                default:
                    $this->_pdo = new \PDO("sqlite::memory");
                    break;
            }
            Logger::get()->log('Succesfully initialized the DB PDO driver (type: ' . Config::get()->getOptionValue('db.adapter') . ')', \Zend_Log::INFO);
        } catch (\Exception $e) {
            Logger::get()->log('Could not initialize the DB PDO driver:' . $e->getMessage(), \Zend_Log::ERR);
        }
    }


    /**
     * Executes a single query
     * @param string $query
     * @return integer
     */
    protected function _dbStatement($sql, $params = array()) {
        Logger::get()->log('Executing SQL Statement: ' . $sql . ' with params: ' . var_export($params, true), \Zend_Log::DEBUG);
        $this->getPdo()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        try {
            $this->_stmt = $this->getPdo()->prepare($sql);
            foreach($params as $name => $value) {
                $this->_stmt->bindParam(':' . $name, $value);
            }
            return $this->_stmt->execute($params);
        } catch (\Exception $e) {
            Logger::get()->log('Failed to execute SQL Statement: ' . $e->getMessage(), \Zend_Log::DEBUG);
        }
    }

}
