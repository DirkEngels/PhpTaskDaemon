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
    public function __construct($jobId = null, Data\AbstractClass $input = null) {
        if ($jobId === null) {
            $jobId = $this::generateJobId();
        }
        if ($input === null) {
            $input = new Data\BaseClass();
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
    public function setJobId($jobId = null) {
        if ($jobId===null) {
            $jobId = $this->generateJobId();
        }
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
        $this->_input->set($input);
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
    public function setOutput(Data\AbstractClass $output) {
        $this->_output->set($output);
    }

}