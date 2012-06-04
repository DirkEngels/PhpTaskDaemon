<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Pid
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon\Pid;

use \PhpTaskDaemon\Exception;

/**
 * The Daemon Pid File object is responsible for reading and writing
 * the process ID to a file. Currently only the main daemon and it's managers
 * use this class to store the process IDs to disk. The task do not write a pid
 * to disk.
 */
class File {

    /**
     * The location of the pidfile. This is only used by the main and its 
     * managers daemon to storing the process ID to a file.
     * 
     * @var string Name of the file to store the Process ID in.
     */
    protected $_filename = NULL;


    /**
     * The pid reader constructor has one optional argument containing a 
     * filename.
     * 
     * @param string $filename
     * @return string
     */
    public function __construct($filename = NULL) {
        return $this->setFilename($filename);
    }


    /**
     * The main daemon saves its pid into a pidfile. This methods returns the
     * filename of the pidfile.
     * 
     * @return NULL|string The absolute filename or not.
     */
    public function getFilename() {
        if ($this->_filename == NULL) {
            $this->_filename = \TMP_PATH . '/daemon.pid';
        }

        // Adjust relative paths.
        if (substr( $this->_filename, 0, 1 ) != '/' ) {
            $this->_filename = \APPLICATION_PATH . $this->_filename;
        }

        return $this->_filename;
    }


    /**
     * Sets the location of the pidfile for storing the pid of the main daemon.
     * 
     * @param string $filename
     * @return bool
     */
    public function setFilename($filename) {
        if (isset($filename)) {
            $this->_filename = $filename;
            return TRUE;
        }
        return FALSE;
    }


    /**
     * Checks if a process has written a pidfile.
     * 
     * @return bool
     */
    public function isRunning() {
        $pid = $this->read();
        if ($pid>0) {
            return TRUE;
        }
        return FALSE;
    }


    /**
     * Reads the pid file and returns the process ID.
     * 
     * @return int
     */
    public function read() {
        $pid = 0;
        if (!file_exists($this->getFilename())) {
            throw new Exception\FileNotFound('Pidfile not found');
        } else {
            $pid = (int) file_get_contents($this->getFilename());
            return $pid;
        }
        return NULL;
    }


    /**
     * Removes the pidfile. Returns FALSE if the file does not exists or cannot
     * be removed.
     * 
     * @return bool
     */
    public function unlink() {
        if (!file_exists($this->getFilename())) {
            throw new Exception\FileNotFound( 'Pidfile not found' );
        } else {
            return unlink( $this->getFilename() );
        }
        return FALSE;
    }


    /**
     * Writes a file to disk containing the process ID.
     * 
     * @param int $pid
     * @return bool
     */
    public function write( $pid = NULL ) {
        if ( $pid > 0 ) {
            if ( ! file_exists( $this->getFilename() ) ) {
                touch( $this->getFilename() );
            }

            file_put_contents( $this->getFilename(), $pid );
            return TRUE;
        }
        return FALSE;
    }

}
