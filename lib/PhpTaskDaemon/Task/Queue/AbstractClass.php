<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Queue
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Task\Queue;

abstract class AbstractClass {
	protected $_statistics;
	
	public function __construct($statistics = null) {
		$this->setStatistics($statistics);
	}
	public function getStatistics() {
		return $this->_statistics;
	}
	public function setStatistics($statistics) {
		$this->_statistics = $statistics;
	}
	public function updateStatistics($status, $count = null) {
		if ($this->_statistics != null) {
			if ($count != null) {
				$this->_statistics->setStatusCount($status, $count);
			} else {
				$this->_statistics->incrementStatus($status);
			}
		}
		return false;
	}
	public function updateQueue($count = null) {
		if ($this->_statistics != null) {
			if ($count != null) {
				$this->_statistics->setQueueCount($count);
			} else {
				$this->_statistics->decrementQueue();
			}
		}
		return true;
	}

}