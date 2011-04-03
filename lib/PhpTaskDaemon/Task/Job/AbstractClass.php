<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Job
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */
namespace PhpTaskDaemon\Job;

abstract class AbstractClass {
	protected $_jobId;
	protected $_input = array();
	protected $_output = array();
	
	
	public function __construct($jobId = null, $input = array()) {
		if ($jobId == null) {
			$jobId = $this->_generateJobId();
		}
		$this->setJobId($jobId);
		$this->setInput($input);
	}

	/**
	 * 
	 * Generates a random job Id
	 * @return string
	 */
	public function generateJobId() {
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
		return $this->_output[$var];
	}

	/**
	 * 
	 * (Re)Sets a single output key
	 */
	public function setOutputVar($var, $value) {
		$this->_output[$var] = $value;
	}

}