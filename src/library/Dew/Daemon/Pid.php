<?php

/**
 * @author DirkEngels
 * @package Dew
 * @subpackage Dew_Daemon
 */

/**
 * This Dew_Daemon_Pid object is responsible for reading and writing the process
 * ID to a file. Currently only the main daemon and it's managers store it's 
 * process ID to disk.
 */
class Dew_Daemon_Pid {

	/**
	 * This variables stores the current process ID.
	 * @var int|null
	 */
	protected $_current = null;
	
	/**
	 * 
	 * The parent process ID
	 * @var int|null
	 */
	protected $_parent = null;

	/**
	 * An array with the child pids, if any.
	 * @var array
	 */
	protected $_childs = array();

	/**
	 * The location of the pidfile. This is only used by the main and its 
	 * managers daemon to storing the process ID to a file.
	 * @var string
	 */
	protected $_filename = null;

	/**
	 * 
	 * Constructor
	 * @param int $pid
	 * @param int $parent
	 */
	public function __construct($pid = null, $parent = null) {
		if ($pid !== null) {
			$this->_current = $pid;
		}
		if ($parent !== null) {
			$this->_current = $pid;
		}
	}
	
	/**
	 * 
	 * Returns the PID of the current process
	 * @return int
	 */
	public function getCurrent() {
		return $this->_current;
	}
	
	/**
	 * 
	 * Returns the PID of the parent process
	 * @return int|null
	 */
	public function getParent() {
		return $this->_parent;
	}
	
	/**
	 * 
	 * Returns an array with pids of child processes, if any.
	 * @return array
	 */
	public function getChilds() {
		return $this->_childs;
	}

	/**
	 * 
	 * Checks if the current process has any child processes.
	 * @return bool
	 */
	public function hasChilds() {
		if (count($this->_childs)>0) {
			return true;
		}
		return false;
	}
	
	/**
	 * 
	 * Adds a PID of a child process
	 * @param int $pid
	 * @return int
	 */
	public function addChild($pid) {
		return array_push($this->_childs, $pid);
	}
	
	/**
	 * 
	 * Removes a PID of a child process
	 * @param int $pid
	 * @return bool
	 */
	public function removeChild($pid) {
		$key = array_search($pid, $this->_childs);
		if ($key !== false) {
			unset($this->_childs[$key]);
			return true;
		}
		return false;
	}

	/**
	 * This method should be called by the child when a process has forked. It
	 * shifts the current process ID to the parent and sets the new child 
	 * process ID. It also cleans all existing childs.
	 */
	public function forkChild($newPid) {
		$this->_parent = $this->_current;
		$this->_current = $newPid;
		$this->_childs = array();
	}
	
	/**
	 * 
	 * The main daemon saves its pid into a pidfile. This methods returns the
	 * filename of the pidfile.
	 * @return string
	 */
	public function getFilename() {
		$filename = $this->_filename;
		if ($filename == null) {
			$filename = realdir(APPLICATION_PATH . '/../tmp/') . strtolower($this->_filename) . '.pid';
			echo $filename;
		}
		return $filename;
	}
	/**
	 * 
	 * Sets the location of the pidfile for storing the pid of the main daemon
	 * @param string $filename
	 * @return bool
	 */
	public function setFilename($filename) {
		touch($filename);
		if (file_exists($filename)) {
			$this->_filename = $filename;
			return true;
		}
		return false;
	}
	
	/**
	 * Checks if a process has written a pidfile
	 * @return bool
	 * 
	 */
	public function isRunning() {
		$pid = $this->readPidFile();
		if ($pid>0) {
			return true;
		}
		return false;
	}

	/**
	 * 
	 * Reads the pid file and returns the process ID
	 * @param string $this->_filename
	 * @return int
	 */
	public function readPidFile() {
		if (file_exists($this->getFilename())) {
			$this->_current = (int) file_get_contents($this->getFilename());
			return $this->_current;
		}
		return null;
	}
	
	/**
	 * 
	 * Removes the pidfile. Returns false if the file does not exists or cannot
	 * be removed.
	 * @param unknown_type $this->_filename
	 * @return bool
	 */
	public function unlinkPidFile() {
		if (file_exists($this->getFilename())) {
			return unlink($this->getFilename());
		}
		return false;
	}

	/**
	 * 
	 * Writes a file to disk containing the process ID.
	 * @param int $pid
	 * @param string $this->_filename
	 * @return bool
	 */
	public function writePidFile($pid = null) {
		if ($pid == null) {
			$pid = $this->_current;
		}

		if ($pid>0) {
			if (!file_exists($this->getFilename())) {
				touch($this->getFilename());
			}

			file_put_contents($this->getFilename(), $pid);
			return true;
		}
		return false;
	}
}