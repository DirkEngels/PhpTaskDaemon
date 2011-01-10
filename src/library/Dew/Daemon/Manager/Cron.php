<?php

/**
 * 
 * This class represents a manager which runs periodically based on a preset 
 * interval in cron.  
 * 
 * 
 * @author DirkEngels <d.engels@dirkengels.com> 
 * 
 */
class Dew_Daemon_Manager_Cron extends Dew_Daemon_Manager_Interval {
	
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
		
		$sleepTime = $this->_getNextTime()-mktime();
//		echo "Sleepy time:\n";
//		echo "- Current: " . mktime() . "\n";
//		echo "- Next: " . $this->_getNextTime() . "\n";
//		echo "- Wait: " . $sleepTime . "\n";

		// Sleep
		echo "Sleeping <cron> for : " . $sleepTime . "\n";
		sleep($sleepTime);
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
		$nextTime = mktime(
			$cron['hour'], $cron['minute'], $cron['second'], 
			$cron['month'], $cron['day'], $cron['year']
		);
		
		foreach ($cron as $field => $value) {
			//echo "CHECK : " . $nextTime . " <=> " . $currentTime . "\n";
			if ($nextTime > $currentTime) {
				$newTime = $nextTime;
			} else {
//				echo "CheckingField: " . $field . "\n";
				switch($field) {
					case 'second':
						$nextTime++;
						break;
					case 'minute':
						$nextTime += 60;
						break;
					case 'hour':
						$nextTime += 3600;
						break;
					case 'day':
						$nextTime = mktime(
							$cron['hour'], $cron['minute'], $cron['second'], 
							$cron['month'], $cron['day']+1, $cron['year']
						);
						break;
					case 'month':
						$nextTime = mktime(
							$cron['hour'], $cron['minute'], $cron['second'], 
							$cron['month']+1, $cron['day'], $cron['year']
						);
						break;
					case 'year':
						$nextTime = mktime(
							$cron['hour'], $cron['minute'], $cron['second'], 
							$cron['month'], $cron['day'], $cron['year']+1
						);
						break;
					default:
						break;
				}
			}
		}
		
		return $newTime;
	}

}
