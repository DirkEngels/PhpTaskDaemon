<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Executor
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Executor;

abstract class AbstractClass {
	protected $_job = null;
	protected $_status = null;
	
	public function __construct($job, $status = null) {
		$this->setJob($job);
		$this->setStatus($status);
	}
	public function getJob() {
		return $this->_job;
	}
	public function setJob($job) {
		$this->_job = $job;
	}
	public function getStatus() {
		return $this->_status;
	}
	public function setStatus($status) {
		$this->_status = $status;
	}

	public function updateStatus($percentage, $message = null) {
		if ($this->_status != null) {
			return $this->_status->set($percentage, $message);
		}
		return false;
	}

	
}