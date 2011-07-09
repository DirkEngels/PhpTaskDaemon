<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Job
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Job;

use \PhpTaskDaemon\Task\Job\Data as Data;

/**
 * 
 * The job abstract class provides setters and getters for the input and output
 * variables. The abstract class also implements a check method for the input
 * used by the managers. 
 */
abstract class AbstractClass {
    protected $_jobId;
    protected $_input = null;
    protected $_output = null;


    /**
     * The first argument contains the jobId, used for identifying the job. If
     * no jobId is provided, an random ID will be generated. The second 
     * argument is an array containing the input variables.
     * @param string $jobId
     * @param array $input 
     */
    public function __construct($jobId = null, $inputData = null) {
        if ($jobId === null) {
            $jobId = $this::generateJobId();
        }

        $input = new \PhpTaskDaemon\Task\Job\Data\BaseClass();
        if (is_array($inputData)) {
            $input->set($inputData);
        }

        $this->setJobId($jobId);
        $this->setInput($input);
        $this->_output = new \PhpTaskDaemon\Task\Job\Data\BaseClass();
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
    public function setJobId($jobId = null) {
        if ($jobId===null) {
            $jobId = $this->generateJobId();
        }
        $this->_jobId = $jobId;
        return $this;
    }


    /**
     * 
     * Returns an data object with input variables
     * @return \PhpTaskDaemon\Task\Job\Data
     */
    public function getInput() {
        return $this->_input;
    }


    /**
     * 
     * (Re)Sets the input array 
     * @param \PhpTaskDaemon\Task\Job\Data $input
     * @return bool
     */
    public function setInput($input) {
        if (is_a($input, '\PhpTaskDaemon\Task\Job\Data\AbstractClass')) {
            $this->_input = $input;
            return true; 
        }
        return false;
    }


    /**
     * 
     * Returns an data object with output variables
     * @return \PhpTaskDaemon\Task\Job\Data
     */
    public function getOutput() {
        return $this->_output;
    }


    /**
     * 
     * (Re)Sets the output array 
     * @param \PhpTaskDaemon\Task\Job\Data $output
     * @return bool
     */
    public function setOutput($output) {
        if (is_a($output, '\PhpTaskDaemon\Task\Job\Data\AbstractClass')) {
            $this->_output = $output;
            return true; 
        }
        return false;
    }

}