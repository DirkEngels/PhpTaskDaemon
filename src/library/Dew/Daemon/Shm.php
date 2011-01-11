<?php
/**
 * @author DirkEngels <d.engels@dirkengels.com>
 * @package Dew
 * @subpackage Dew_Daemon
 */

/**
 * 
 * The Dew_Daemon_Shm class is responsible for storing and retreiving shared
 * memory segments. The keys of the shared memory variables are stored in an
 * array.
 */
class Dew_Daemon_Shm {
	/**
	 * This variable contains the identifier string.
	 * @var string|null
	 */
	protected $_identifier = null;
	
	/**
	 * The actual resource of the shared memory segment
	 * @var resource
	 */
	protected $_resource = null;
	
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
		$pathname = TMP_PATH . '/' . strtolower($id) . '.shm';
		touch($pathname);
		$this->_identifier = $pathname;
		$this->_resource = shm_attach(ftok($this->_identifier, 'a'));
	}

	/**
	 * 
	 * The destructor detaches the shared memory segment.
	 */
	public function __destruct() {
		shm_detach($this->_resource);
	}
	
	/**
	 * 
	 * Returns an array of registered variable keys.
	 * @return array
	 */
	public function getKeys() {
		return array_keys($this->_keys);
	}
	
	/**
	 * 
	 * Returns the value of a registered shared variable or false if it does 
	 * not exists. 
	 * @param string $key
	 * @return mixed|false
	 */
	public function getVar($key) {
//		echo $key . ' => ' . $this->_keys[$key] . "\n";
		if (in_array($key, array_keys($this->_keys))) {
			
			return shm_get_var($this->_resource, $this->_keys[$key]);
		}
		return false;
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
		if (!in_array($key, array_keys($this->_keys))) {
			$this->_keys[$key] = count($this->_keys)+1;
//			echo var_dump($this->_keys);
		}
		return shm_put_var($this->_resource, $this->_keys[$key], $value);	
	}
	
	/**
	 * 
	 * Removes and unregisters a registered shared variable key. 
	 * @param string $key
	 * @return bool|int
	 */
	public function removeVar($key) {
		if (!in_array($key, array_keys($this->_keys))) {
			unset($this->_keys[$key]);
			return shm_remove_var($this->_resource, $this->_keys[$key]);
		}
		return false;
	}
	
	/**
	 * 
	 * Removes a shared memory segment.
	 * @return bool|int
	 */
	public function remove() {
		$ret = shm_remove($this->_resource);
		if (file_exists($this->_identifier)) {
			unlink($this->_identifier);
		}
		return $ret;
	}
}