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
    protected $_input = NULL;
    protected $_output = NULL;


    /**
     * The first argument contains the jobId, used for identifying the job. If
     * no jobId is provided, an random ID will be generated. The second 
     * argument is an array containing the input variables.
     * @param string $jobId
     * @param \PhpTaskDaemon\Task\Job\AbstractClass $input 
     */
    public function __construct($jobId = NULL, $input = NULL) {
        if ($jobId === NULL) {
            $jobId = $this::generateJobId();
        }

        $this->setJobId($jobId);
        if (!is_null($input)) {
            $this->setInput($input);
        }

        $this->_output = new \PhpTaskDaemon\Task\Job\Data\DefaultClass();
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
    public function setJobId($jobId = NULL) {
        if ($jobId===NULL) {
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
        if (!is_a($input, '\PhpTaskDaemon\Task\Job\Data\AbstractClass')) {
            throw new \Exception('Wrong data format for job input!');
        }
        $this->_input = $input;
        return TRUE; 
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
        if (!is_a($output, '\PhpTaskDaemon\Task\Job\Data\AbstractClass')) {
            throw new \Exception('Wrong data format for job output!');
        }
        $this->_output = $output;
        return TRUE; 
    }

}