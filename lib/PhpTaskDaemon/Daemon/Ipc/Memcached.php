<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Ipc
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon\Ipc;

use PhpTaskDaemon\Exception;
use PhpTaskDaemon\Daemon\Config;
use PhpTaskDaemon\Daemon\Logger;

/**
 * The Daemon\Ipc\Memcached class is responsible for storing and retrieving
 * inter process communication data from a memcached server.
 * 
 */
class Memcached extends IpcAbstract implements IpcInterface {

    /**
     * Memcached connection instance.
     * 
     * @var \Memcached
     */
    protected $_memcached = NULL;


    /**
     * Constructor with mandatory IPC identifier.
     *
     * @param string $id
     * @return NULL
     */
    public function __construct($id) {
        parent::__construct($id);
        $this->_memCachedSetup();
    }


    /**
     * (non-PHPdoc)
     *
     * @see PhpTaskDaemon\Daemon\Ipc.IpcAbstract::initResource()
     */
    public function initResource() {
        $this->_memcached = NULL;
        return parent::initResource();
    }


    /**
     * Returns the key value from a memcached server.
     *
     * @param string $name
     * @return NULL
     */
    public function getVar( $name ) {
        $result = $this->_memcached->get( $name );

        if ( is_null( $result ) ) {
            throw new \Exception( 'Memcached key does not exists!');
        }

        return $result;
    }


    /**
     * Stores a key value in the database.
     * 
     * @param string $name
     * @throws InvalidArgumentException Value is not an integer!
     * @return bool
     */
    public function setVar( $name, $value ) {
        $compressData = 0;
        $timeOut = 3600;
        if ( ! $this->_memcached->set( $name, $value, $compressData, $timeOut ) ) {
            throw new \InvalidArgumentException ( 'Error setting memcached key: ' . $name );
        }
        return TRUE;
    }


    /**
     * Increments a key with 1 (or more).
     *
     * @param string $name
     * @throws InvalidArgumentException Value is not an integer!
     * @return bool
     */
    public function incrementVar( $name, $count = 1 ) {
        if ( ! $this->memcached->increment( $name ) ) {
            throw new \InvalidArgumentException ( 'Error setting memcached key: ' . $name );
        }
        return TRUE;
    }


    /**
     * Decrements a key with 1 (or more).
     *
     * @param string $name
     * @throws InvalidArgumentException Value is not an integer!
     * @return bool
     */
    public function decrementVar( $name, $count = 1 ) {
        if ( ! $this->memcached->decrement( $name, $count ) ) {
            throw new \InvalidArgumentException ( 'Error setting memcached key: ' . $name );
        }
        return TRUE;
    }


    /**
     * Removes a key from the memcached server
     *
     * @param string $name
     * @throws InvalidArgumentException Value is not an integer!
     * @return bool
     */
    public function removeVar( $name ) {
        $timeOut = 0;
        if ( ! $this->memcached->delete( $name, $timeOut ) ) {
            throw new \InvalidArgumentException ( 'Error removing memcached key: ' . $name );
        }
        return TRUE;
    }


    /**
     * Removes all key registered known of this memcached server.
     *
     * @throws InvalidArgumentException Value is not an integer!
     * @return bool
     */
    public function remove() {
        $delay = 0;
        if ( ! $this->memcached->flush( $delay = NULL ) ) {
            throw new \InvalidArgumentException ( 'Error removing all memcached key: ' );
        }
        return TRUE;
    }


    /**
     * Sets up the database connection using the credentials from the config.
     * object.
     *
     * @return bool
     */
    protected function _memCachedSetup( $id ) {
        $this->_memcached = new \Memcache();
        return $this->_memcached->connect('localhost', 11211);
    }

}
