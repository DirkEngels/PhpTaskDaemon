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
 * This class extends the interval manager, but runs the task in a different
 * process (sandbox) by a system command.
 */
class System extends Interval {

	protected function _processTask(\PhpTaskDaemon\Task\Job\AbstractClass $job) {
		$this->log("Starting system task: cd " . \TMP_PATH . " && php system -sj '" . $job . "'", \Zend_Log::INFO);
		system("cd " . \APPLICATION_PATH . "/bin && php system -sj '" . $job . "'");
	}
	
	/**
	 * 
	 * The method below is used by the system command. The system 
	 * @param unknown_type $serializedJob
	 */
	public function acceptInputFromConsoleAndStartSingleTask($serializedJob) {
		$this->log("Processing system task: " . $serializedJob, \Zend_Log::DEBUG);
		echo "----------------\n";
		echo $serializedJob . "\n";
		echo "----------------\n";
		$job = new \Tasks\Concept\CopyTask\Job();
		$job->setInputVar('sleepTime', 200000);
		$serializedJob = serialize($job);
		echo "----------------\n";
		echo $serializedJob . "\n";
		echo "----------------\n";
		$job = unserialize($serializedJob);
		if (!is_a($job, '\PhpTaskDaemon\Task\Job\AbstractClass')) {
			echo "Error unserializing job object\n";
			exit(0);
		}
		echo var_dump($job);
//		$ret = parent::_processTask($job);
$ret = 1;
		return $ret;
	}
}