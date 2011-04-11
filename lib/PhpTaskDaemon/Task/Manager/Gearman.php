<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager;

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
	protected $_gearmanAsync = false;

	/**
	 * The amount of workers for running multiple workers in parallel. The
	 * Gearman Manager uses the Forked Manager in order to fork multiple
	 * processes. 
	 */
	protected $_gearmanForks = 1;

	/**
	 * Gearman Job object
	 * 
	 * @var GearmanJob
	 */
	private $_gearmanJob = null;
	
	/**
	 * Runs the manager
	 * @see PhpTaskDaemon\Task\Manager.InterfaceClass::execute()
	 */
	public function execute() {
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
	
	/**
	 * 
	 * Accepts the gearman input and prepares the job input.
	 * @param mixed $gearmanJob
	 */
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
