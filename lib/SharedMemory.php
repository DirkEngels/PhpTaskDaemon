<?php
/**
 * @package SiteSpeed
 * @subpackage Daemon
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace SiteSpeed\Daemon;

/**
 * 
 * The Dew_Daemon_Shm class is responsible for storing and retreiving shared
 * memory segments. The keys of the shared memory variables are stored in an
 * array.
 */
class SharedMemory {
	/**
	 * This variable contains the identifier string.
	 * @var string|null
	 */
	protected $_pathNameWithPid = null;

	/**
	 * The actual resource of the shared memory segment
	 * @var resource
	 */
	protected $_sharedMemory = null;

	/**
	 * The semaphore needed to lock/unlock access to the shared memory
	 * segment.
	 */
	protected $_semaphore = null;
	
	/**
	 * This array contains the keys of all registered variables.
	 * @var array
	 */
	protected $_keys = array();
	
	/**
	 * 
	 * The constructor requires an identifier which is used for attaching to @author dirk
	 * shared memory segment.
	 * @param string $id
	 */
	public function __construct($id) {
		$pathname = TMP_PATH . '/' . strtolower($id);
		$this->_pathNameWithPid = $pathname;

		if (!file_exists($pathname . '.sem')) {
			touch($pathname . '.sem');
		}
		$this->_semaphore = sem_get(
			ftok($this->_pathNameWithPid . '.sem', 1)
		);

		
		if (!file_exists($pathname . '.shm')) {
			touch($pathname . '.shm');
		}
		$this->_sharedMemory = shm_attach(
			ftok($this->_pathNameWithPid . '.shm', 2)
		);

		sem_acquire($this->_semaphore);
		if (!shm_has_var($this->_sharedMemory, 1)) {
			$retInit = shm_put_var($this->_sharedMemory, 1, array());
		}
		sem_release($this->_semaphore);
	}

	/**
	 * 
	 * The destructor detaches the shared memory segment.
	 */
	public function __destruct() {
		if (is_resource($this->_sharedMemory)) {
			shm_detach($this->_sharedMemory);
		}
		if (is_resource($this->_semaphore)) {
//			sem_release($this->_semaphore);
		}
	}
	
	/**
	 * 
	 * Returns an array of registered variable keys.
	 * @return array
	 */
	public function getKeys() {
		sem_acquire($this->_semaphore);
		$keys = shm_get_var($this->_sharedMemory, 1);
		sem_release($this->_semaphore);
		
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
		sem_acquire($this->_semaphore);
		$value = false;
		$keys = shm_get_var($this->_sharedMemory, 1);
		if (in_array($key, array_keys($keys))) {
			$value = shm_get_var($this->_sharedMemory, $keys[$key]);
		}
		sem_release($this->_semaphore);
		
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
		sem_acquire($this->_semaphore);
		// Check the first variable for keys
		if (shm_has_var($this->_sharedMemory, 1)) {
			$keys = shm_get_var($this->_sharedMemory, 1);
		} else {
			$keys = array();
		}
		$retInit = true;
		
		// Update keys
		if (!in_array($key, array_keys($keys))) {
			if (count($keys)==0) {
				$keys[$key] = 2;
			} else {
				$keys[$key] = count($keys)+2;
			}
			$retInit = shm_put_var($this->_sharedMemory, 1, $keys);
		}
		$retPut = shm_put_var($this->_sharedMemory, $keys[$key], $value);
		sem_release($this->_semaphore);
		
		return $retInit && $retPut;
	}
	
	/**
	 * 
	 * Removes and unregisters a registered shared variable key. 
	 * @param string $key
	 * @return bool|int
	 */
	public function removeVar($key) {
		sem_acquire($this->_semaphore);
		$ret = false;
		if (isset($this->_keys[$key])) {
			if (shm_has_var($this->_sharedMemory, $this->_keys[$key])) {
				$ret = shm_remove_var($this->_sharedMemory, $this->_keys[$key]);
			}
			unset($this->_keys[$key]);
		}
		sem_release($this->_semaphore);
		return $ret;
	}
	
	/**
	 * 
	 * Removes a shared memory segment.
	 * @return bool|int
	 */
	public function remove() {
		$retSem = $retShm = false;

		// Remove Shared Memory
		if (is_resource($this->_sharedMemory)) {
			$retShm = shm_remove($this->_sharedMemory);
		}
		if (file_exists($this->_pathNameWithPid . '.shm')) {
			unlink($this->_pathNameWithPid . '.shm');
		}

		// Remove Semaphore
		if (is_resource($this->_semaphore)) {
			$retSem = sem_remove($this->_semaphore);
		}
		if (file_exists($this->_pathNameWithPid . '.sem')) {
			unlink($this->_pathNameWithPid . '.sem');
		}

		return ($retShm && $retSem);
	}
}
