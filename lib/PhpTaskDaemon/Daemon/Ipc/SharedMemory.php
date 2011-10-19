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
 * The Dew_Daemon_Shm class is responsible for storing and retreiving shared
 * memory segments. The keys of the shared memory variables are stored in an
 * array.
 */
class SharedMemory extends AbstractClass implements InterfaceClass {
    /**
     * This variable contains the identifier string.
     * @var string|NULL
     */
    protected $_pathNameWithPid = NULL;

    /**
     * The actual resource of the shared memory segment
     * @var resource
     */
    protected $_sharedMemory = NULL;

    /**
     * The semaphore needed to lock/unlock access to the shared memory
     * segment.
     * @var resource
     */
    protected $_semaphoreLock = NULL;


    /**
     * 
     * The constructor requires an identifier which is used for attaching to a
     * shared memory segment.
     * @param string $id
     */
    public function __construct($id) {
        parent::__construct($id);

        if (!strstr($id, '/')) {
            $pathname = TMP_PATH . '/' . strtolower($id);
        } else {
            $pathname = $id;
        }

        $this->_pathNameWithPid = $pathname;

        // Dommel & Semaphores
        if (!file_exists($this->_pathNameWithPid . '.sem')) {
            touch($this->_pathNameWithPid . '.sem');
        }
        $this->_semaphoreLock = sem_get(
            ftok($this->_pathNameWithPid . '.sem', 1)
        );

        // Shared Memory Segment
        if (!file_exists($this->_pathNameWithPid . '.shm')) {
            touch($this->_pathNameWithPid . '.shm');
        }
        $this->_sharedMemory = shm_attach(
            ftok($this->_pathNameWithPid . '.shm', 2)
        );

        // Save the shared memory variable
        sem_acquire($this->_semaphoreLock);
        if (!shm_has_var($this->_sharedMemory, 1)) {
            $returnValue = shm_put_var($this->_sharedMemory, 1, array());
        }
        sem_release($this->_semaphoreLock);

        return $returnValue;
    }


    /**
     * 
     * The destructor detaches the shared memory segment.
     */
    public function __destruct() {
        if (is_resource($this->_sharedMemory)) {
            shm_detach($this->_sharedMemory);
        }
    }


    /**
     * 
     * Returns an array of registered variable keys.
     * @return array
     */
    public function getKeys() {
        sem_acquire($this->_semaphoreLock);
        $keys = parent::getKeys();
        if (shm_has_var($this->_sharedMemory, 1)) {    
            $keys = shm_get_var($this->_sharedMemory, 1);
        }
        sem_release($this->_semaphoreLock);

        return $keys;
    }


    /**
     * 
     * Returns the value of a registered shared variable or false if it does 
     * not exists. 
     * @param string $key
     * @return mixed|false
     */
    public function getVar($key) {
        $key = strtolower($key);
        sem_acquire($this->_semaphoreLock);
        $value = false;
        $keys = shm_get_var($this->_sharedMemory, 1);
        if (in_array($key, array_keys($keys))) {
            $value = shm_get_var($this->_sharedMemory, $keys[$key]);
        }
        sem_release($this->_semaphoreLock);

        return $value;
    }


    /**
     * 
     * Sets the value of a shared variable. It registers the variable key when
     * it does not yet exists.
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function setVar($key, $value) {
        $key = strtolower($key);
        sem_acquire($this->_semaphoreLock);
        // Check the first variable for keys
        $keys = array('keys' => 1);
        if (shm_has_var($this->_sharedMemory, 1)) {
            $keys = shm_get_var($this->_sharedMemory, 1);
        }
        $retInit = TRUE;

        // Update keys
        if (!in_array($key, array_keys($keys))) {
            $keys[$key] = count($keys)+2;
            $retInit = shm_put_var($this->_sharedMemory, 1, $keys);
        }
        $retPut = shm_put_var($this->_sharedMemory, $keys[$key], $value);
        sem_release($this->_semaphoreLock);

        return $retInit && $retPut;
    }


    /**
     * 
     * Increments the value of a shared variable.
     * @param string $key
     * @return bool|int
     */
    public function incrementVar($key) {
        $key = strtolower($key);

        sem_acquire($this->_semaphoreLock);
        // Check the first variable for keys
        $keys = array('keys' => 1);
        if (shm_has_var($this->_sharedMemory, 1)) {
            $keys = shm_get_var($this->_sharedMemory, 1);
        }

        // Update keys
        if (in_array($key, array_keys($keys))) {
            $value = shm_get_var($this->_sharedMemory, $keys[$key]);
            $value++;
            $returnValue = shm_put_var($this->_sharedMemory, $keys[$key], $value);
        } else {
            $keys[$key] = count($keys)+2;
            shm_put_var($this->_sharedMemory, 1, $keys);
            $returnValue = shm_put_var($this->_sharedMemory, $keys[$key], 1);
            $value = 1;
        }
        sem_release($this->_semaphoreLock);

        return $returnValue;
    }


    /**
     * 
     * Decrements the value of a shared variable.
     * @param string $key
     * @return bool|int
     */
    public function decrementVar($key) {
        $key = strtolower($key);
        sem_acquire($this->_semaphoreLock);

        // Check the first variable for keys
        $keys = array('keys' => 1);
        if (shm_has_var($this->_sharedMemory, 1)) {
            $keys = shm_get_var($this->_sharedMemory, 1);
        }
        $retInit = TRUE;

        // Update keys
        $value = 0;
        if (!in_array($key, array_keys($keys))) {
            $keys[$key] = count($keys)+2;
            $retInit = shm_put_var($this->_sharedMemory, 1, $keys);
        } else {
            $value = shm_get_var($this->_sharedMemory, $keys[$key]);
            $value--;
            if ($value<0) { $value = 0; }
        }
        $retPut = shm_put_var($this->_sharedMemory, $keys[$key], $value);
        sem_release($this->_semaphoreLock);
        
        return $retInit && $retPut;
    }


    /**
     * 
     * Removes and unregisters a registered shared variable key. 
     * @param string $key
     * @return bool|int
     */
    public function removeVar($key) {
        $key = strtolower($key);
        $ret = false;
        $keys = $this->getKeys();
        if (isset($keys[$key])) {
            sem_acquire($this->_semaphoreLock);
            if (shm_has_var($this->_sharedMemory, $keys[$key])) {
                $ret = shm_remove_var($this->_sharedMemory, $keys[$key]);

                // update 
                unset($keys[$key]);
                shm_put_var($this->_sharedMemory, 1, $keys);
            }
            unset($this->_keys[$key]);
            sem_release($this->_semaphoreLock);
        }
        return $ret;
    }


    /**
     * 
     * Removes a shared memory segment and semaphore
     * @return bool|int
     */
    public function remove() {
        return ($this->_removeSegment() && $this->_removeSemaphore());
    }


    /**
     * 
     * Removes a shared memory segment
     * @return bool|int
     */
    private function _removeSegment() {
        $ret = false;
        if (is_resource($this->_sharedMemory)) {
            if (file_exists($this->_pathNameWithPid . '.shm')) {
                $ret = shm_remove($this->_sharedMemory);
                unlink($this->_pathNameWithPid . '.shm');
            }
        }
        return $ret;
    }


    /**
     * 
     * Removes a semaphore required for the shared memory segment.
     * @return bool|int
     */
    private function _removeSemaphore() {
        $ret = false;
        if (is_resource($this->_semaphoreLock)) {
            if (file_exists($this->_pathNameWithPid . '.sem')) {
                $ret = sem_remove($this->_semaphoreLock);
                unlink($this->_pathNameWithPid . '.sem');
            }
        }
        return $ret;
    }

}
