<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Pid
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon\Pid;

/**
 * This SiteSpeed Daemon Pid File object is responsible for reading and writing
 * the process ID to a file. Currently only the main daemon and it's managers
 * use this class to store the process IDs to disk. The task do not write a pid
 * to disk.
 */
class File {

	/**
	 * The location of the pidfile. This is only used by the main and its 
	 * managers daemon to storing the process ID to a file.
	 * @var string
	 */
	protected $_filename = null;
		

	/**
	 * 
	 * The pid reader constructor has one optional argument containing a 
	 * filename.
	 * @param string $filename
	 */
	public function __construct($filename = null) {
		$this->setFilename($filename);
	}

	/**
	 * 
	 * The main daemon saves its pid into a pidfile. This methods returns the
	 * filename of the pidfile.
	 * @return string
	 */
	public function getFilename() {
		if ($this->_filename == null) {
			$this->_filename = \TMP_PATH . '/daemon.pid';
		}
		return $this->_filename;
	}
	/**
	 * 
	 * Sets the location of the pidfile for storing the pid of the main daemon
	 * @param string $filename
	 * @return bool
	 */
	public function setFilename($filename) {
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
	 * @return int
	 */
	public function read() {
		if (file_exists($this->getFilename())) {
			$pid = (int) file_get_contents($this->getFilename());
			if ($pid>0) {
				return $pid;
			}
		}
		return null;
	}
	
	/**
	 * 
	 * Removes the pidfile. Returns false if the file does not exists or cannot
	 * be removed.
	 * @return bool
	 */
	public function unlink() {
		if (file_exists($this->getFilename())) {
			return unlink($this->getFilename());
		}
		return false;
	}

	/**
	 * 
	 * Writes a file to disk containing the process ID.
	 * @param int $pid
	 * @return bool
	 */
	public function write($pid = null) {		
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