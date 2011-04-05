<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Task\Job
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Task\Job;

abstract class AbstractClass {
	protected $_jobId;
	protected $_input = array();
	protected $_inputKeys = array();
	protected $_output = array();
	
	
	public function __construct($jobId = null, $input = array()) {
		if ($jobId == null) {
			$jobId = $this::generateJobId();
		}
		$this->setJobId($jobId);
		$this->setInput($input);
	}

	/**
	 * 
	 * Generates a random job Id
	 * @return string
	 */
	public static function generateJobId() {
		return substr(md5(uniqid()), 0,10);
	}

	/**
	 * 
	 * Returns the current job ID
	 * @return string
	 */
	public function getJobId() {
		return $this->_jobId;
	}

	/** 
	 * 
	 * Sets a new job ID
	 * @param string $jobId
	 * @return $this;
	 */
	public function setJobId($jobId) {
		$this->_jobId = $jobId;
		return $this;
	}
	
	/**
	 * 
	 * Returns an array with input variables
	 * @return array
	 */
	public function getInput() {
		return $this->_input;
	}
	
	/**
	 * 
	 * (Re)Sets the input array 
	 * @param array $input
	 */
	public function setInput($input) {
		$this->_input = $input;
	}

	/**
	 * 
	 * (Re)Sets a single input key
	 */
	public function getInputVar($var) {
		return $this->_input[$var];
	}

	/**
	 * 
	 * (Re)Sets a single input key
	 */
	public function setInputVar($var, $value) {
		$this->_input[$var] = $value;
	}

	/**
	 * 
	 * Returns the input field keys
	 */
	public function getInputKeys() {
		return $this->_inputKeys;
	}

	/**
	 * 
	 * Sets the input field keys
	 * @param array $inputKeys
	 */
	public function setInputKeys($inputKeys) {
		$this->_inputKeys = $inputKeys;
	}
	
	/**
	 * 
	 * Returns an array with output variables
	 * @return array
	 */
	public function getOutput() {
		return $this->_output;
	}
	
	/**
	 * 
	 * (Re)Sets the output array 
	 * @param array $output
	 */
	public function setOutput($output) {
		$this->_output = $output;
	}
	
	/**
	 * 
	 * Returns a single output variable
	 * @param mixed $var
	 */
	public function getOutputVar($var) {
		$value = (isset($this->_output[$var])) ? $this->_output[$var] : '?'; 
		return $value;
	}

	/**
	 * 
	 * (Re)Sets a single output key
	 */
	public function setOutputVar($var, $value) {
		$this->_output[$var] = $value;
	}

	/**
	 * 
	 * Check the input array for the needed keys; in this case only a sleepTime
	 * variable is expected
	 * @return bool;`
	 */
	public function checkInput() {
		foreach($this->_inputKeys as $key) {
			if (!array_key_exists($key, $this->_input)) {
				return false;
			}
		}
		return true;
	}
}