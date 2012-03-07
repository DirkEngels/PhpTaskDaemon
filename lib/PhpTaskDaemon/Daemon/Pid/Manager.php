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
 * This Pid Manager object manages process ID's of the current, parent and 
 * child processes. When forking processes, the forkChild() method of this
 * class can be called to shift the current pid to parent and empty any child 
 * pids.
 */
class Manager {

    /**
     * This variables stores the current process ID.
     * 
     * @var int|NULL
     */
    protected $_current = NULL;

    /**
     * The parent process ID.
     * 
     * @var int|NULL
     */
    protected $_parent = NULL;

    /**
     * An array with the child pids, if any.
     * 
     * @var array
     */
    protected $_childs = array();


    /**
     * Constructor with optional process (and parent) id.
     * 
     * @param int $pid
     * @param int $parent
     */
    public function __construct($pid = NULL, $parent = NULL) {
        if ($pid === NULL) {
            $pid = getmypid();
        }
        $this->_current = $pid;
        if ($parent !== NULL) {
            $this->_parent = $parent;
        }
    }


    /**
     * Returns the PID of the current process.
     * 
     * @return int
     */
    public function getCurrent() {
        return $this->_current;
    }


    /**
     * Returns the PID of the parent process.
     * 
     * @return int|NULL
     */
    public function getParent() {
        return $this->_parent;
    }


    /**
     * Returns an array with pids of child processes, if any.
     * 
     * @return array
     */
    public function getChilds() {
        return $this->_childs;
    }


    /**
     * Checks if the current process has any child processes.
     * 
     * @return bool
     */
    public function hasChilds() {
        if (count($this->_childs)>0) {
            return TRUE;
        }
        return FALSE;
    }


    /**
     * Adds a PID of a child process.
     * 
     * @param int $pid
     * @return int
     */
    public function addChild($pid) {
        return array_push($this->_childs, $pid);
    }


    /**
     * Removes a PID of a child process.
     * 
     * @param int $pid
     * @return bool
     */
    public function removeChild($pid) {
        $key = array_search($pid, $this->_childs);
        if ($key !== FALSE) {
            unset($this->_childs[$key]);
            return TRUE;
        }
        return FALSE;
    }


    /**
     * This method should be called by the child when a process has forked. It
     * shifts the current process ID to the parent and sets the new child 
     * process ID. It also cleans all existing childs.
     * 
     * @param integer $newPid
     */
    public function forkChild($newPid) {
        $this->_parent = $this->_current;
        $this->_current = $newPid;
        $this->_childs = array();
    }

}