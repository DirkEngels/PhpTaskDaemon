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
	public function updateStatistics($status) {
		if ($this->_statistics != null) {
			return $this->_statistics->increment($status);
		}
		return false;
	}
}