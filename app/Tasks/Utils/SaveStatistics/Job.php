<?php

namespace Utils\SaveStatistics;

class Job extends \PhpTaskDaemon\Job\AbstractClass implements \PhpTaskDaemon\Job\InterfaceClass {

	protected $_inputFields = array('sleepTime');
	
	/**
	 * 
	 * Check the input array for the needed keys; in this case only a sleepTime
	 * variable is expected
	 * @return bool;`
	 */
	public function checkInput() {
		return array_key_exists('sleepTime', $this->_input);
	}
}

