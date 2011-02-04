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
 * This class represents a manager which runs as a gearman job.
 * 
 * @author DirkEngels <d.engels@dirkengels.com> 
 * 
 */
class Gearman extends AbstractClass implements InterfaceClass {
	
	/**
	 * Run as job async. Do not wait for the result.
	 * 
	 * @var boolean
	 */
	protected $_async = false;

	/**
	 * Gearman Job object
	 * 
	 * @var GearmanJob
	 */
	protected $_gearmanJob = null;
	
	public function __construct() {
		parent::__construct();
	}

	public function executeManager() {
		$this->_task->updateMemoryTask(0);
		
		$gmworker= new GearmanWorker();
		$gmworker->addServer();
		
		$name = preg_replace('/Daemon_Manager_/i', '', get_class($this));
		$gmworker->addFunction($name, array($this, 'acceptJob'));
	
		while($gmworker->work()) {
			if ($gmworker->returnCode() != GEARMAN_SUCCESS) {
				echo "return_code: " . $gmworker->returnCode();
				break;
			}
		}
				
	}
	public function acceptJob($gearmanJob) {
		$this->_task->setTaskInput($gearmanJob->workload());
		$this->_task->updateMemoryTask(0);

		$ret = $this->_task->executeTask();

		$this->_task->updateMemoryTask(100);
		usleep(10);
		$this->_task->updateMemoryTask(0);
		
		return $ret;
	}	
}
