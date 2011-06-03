\<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Manager\Process
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Manager\Process;

class Parallel extends AbstractClass implements InterfaceClass {
	protected $_maxProcess = 3;


	public function run() {
		$currentChilds = 0;
		
		while(count($this->_jobs)>0) {
			if ($currentChilds<$this->_maxProcess) {
				$job = array_shift($this->_jobs);
				$this->_forkTask($job);
			}
		}
		for ($i = $currentChilds; $i<$this->_maxProcess; $i++) {
            $this->_forkTask($job);
            $currentChilds++;
		}
		
		$this->_executeTask($this->getJob());
	}
}