<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Statistics
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Queue\Statistics;

abstract class AbstractClass {
	protected $_statistics = array();
	
	const STATUS_DONE = 'Done';
	const STATUS_FAILED = 'Failed';

	public function __construct() {
		$this->_initializeStatus(self::STATUS_DONE);
		$this->_initializeStatus(self::STATUS_FAILED);
	}

	/**
	 * Returns an array with the number of executed tasks grouped per status.
	 * 
	 * @param array $status
	 * @return array
	 */
    public function get($status = null) {
    	if ($status != null) {
    		$this->_initializeStatus($status);
    		return $this->_statistics[$status];
    	}
    	return $this->_statistics;
    }

	/**
	 * Increments the statistics for a certain status
	 * 
	 * @param string $status
	 * @return integer
	 */
    public function increment($status = self::STATUS_DONE) {
    	$this->_initializeStatus($status);
    	$this->_statistics[$status]++;
    	return $this->_statistics[$status];
    }
    
    /**
     * Initializes the statistics array for a certain status.
     * 
     * @param string $status
     * @return bool
     */
    private function _initializeStatus($status) {
    	if (isset($this->_statistics[$status])) {
			$this->_statistics[$status] = 0;
			return true;
		}
		return false;
    }
}