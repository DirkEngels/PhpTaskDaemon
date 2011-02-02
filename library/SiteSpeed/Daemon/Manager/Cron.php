<?php

/**
 * @package SiteSpeed
 * @subpackage Daemon\Manager
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace SiteSpeed\Daemon\Manager;

/**
 * 
 * This class represents a manager which runs periodically based on a preset 
 * interval in cron.  
 * 
 */
class Cron extends Interval {
	
	protected $_second = 1;
	protected $_minute = null;
	protected $_hour = null;
	protected $_dom = null;
	protected $_day = null;
	protected $_month = null;
	protected $_year = null;


	/**
	 * 
	 * The sleep function for a cron manager: it dispatches the calculation of
	 * the next sleep time to another function.
	 */
	protected function _sleep() {
		$sleepTime = $this->_getNextTime();
		if ($sleepTime == -1) {
			$sleepTime = 300;
		}
		time_sleep_until($sleepTime);
	}
	
	protected function _getNextTime() {
		$cron['second'] = ($this->_second !== null) ? $this->_second : date('s');
		$cron['minute'] = ($this->_minute !== null) ? $this->_minute : date('i');
		$cron['hour'] = ($this->_hour !== null) ? $this->_hour : date('G');
		$cron['dom'] = ($this->_dom !== null) ? $this->_dom : date('N');
		$cron['day'] = ($this->_day !== null) ? $this->_day : date('j');
		$cron['month'] = ($this->_month !== null) ? $this->_month : date('n');
		$cron['year'] = ($this->_year !== null) ? $this->_year : date('Y');

		$currentTime = mktime();
		$nextTime = 0;
		$durations = array(
			'second' => 1, 'minute' => 60, 'hour' => 3600,
			'day' => 86400, 
		);
		foreach ($durations as $type => $duration) {
			$nextTime = mktime(
				$cron['hour'], $cron['minute'], $cron['second'], 
				$cron['month'], $cron['day'], $cron['year']
			);
			$nextTime += $durations[$type];

			if ($nextTime>$currentTime) {
				return $nextTime;
			}
		}
		// Finished running!
		return -1;
	}

}
