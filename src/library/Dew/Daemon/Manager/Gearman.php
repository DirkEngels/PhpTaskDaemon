<?php

/**
 * 
 * This class represents a manager which runs as a gearman job.
 * 
 * @author DirkEngels <d.engels@dirkengels.com> 
 * 
 */
class Dew_Daemon_Manager_Gearman extends Dew_Daemon_Manager_Abstract implements Dew_Daemon_Manager_Interface {
	
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
	

	public function executeManager() {
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
		echo "sdfdsdfs\n";
	}
	
}
