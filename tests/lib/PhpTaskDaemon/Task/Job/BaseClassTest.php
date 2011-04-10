<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Task\Job
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Task
 * @group PhpTaskDaemon-Task-Job
 */

namespace PhpTaskDaemon\Task\Job;

class JobTest extends \PHPUnit_Framework_Testcase {
	protected $_job;
	
	protected function setUp() {
	}
	protected function tearDown() {
	}
	
	public function testConstructorNoArguments() {
		$this->_job = new \PhpTaskDaemon\Task\Job\BaseClass();
		$this->assertNotEquals('', $this->_job->getJobId());
		$this->assertNotNull('', $this->_job->getJobId());
		$this->assertEquals(0, sizeof($this->_job->getInput()));
		$this->assertEquals(0, sizeof($this->_job->getOutput()));
	}
	public function testConstructorSingleArguments() {
		$this->_job = new \PhpTaskDaemon\Task\Job\BaseClass('test');
		$this->assertEquals('test', $this->_job->getJobId());
		$this->assertEquals(0, sizeof($this->_job->getInput()));
		$this->assertEquals(0, sizeof($this->_job->getOutput()));
	}
	public function testConstructorTwoArguments() {
		$input = array('testVar' => '1234');
		$this->_job = new \PhpTaskDaemon\Task\Job\BaseClass('test', $input);
		$this->assertEquals('test', $this->_job->getJobId());
		$this->assertEquals(1, sizeof($this->_job->getInput()));
		$this->assertEquals(serialize($input), serialize($this->_job->getInput()));
		$this->assertEquals(0, sizeof($this->_job->getOutput()));
	}
	
	public function testGenerateJobId() {
		$this->_job = new \PhpTaskDaemon\Task\Job\BaseClass();
		$jobId = $this->_job->getJobId();
		$this->assertNotEquals('', $jobId);
		$this->assertNotNull('', $jobId);
		$this->_job->setJobId($this->_job->generateJobId());
		$this->assertNotEquals($jobId, $this->_job->getJobId());
	}
	
	public function testSetJobId() {
		$this->_job = new \PhpTaskDaemon\Task\Job\BaseClass('test');
		$this->assertNotNull($this->_job->getJobId());
		$this->_job->setJobId('test');
		$this->assertEquals('test', $this->_job->getJobId());
	}

	public function testSetInputKeys() {
		$this->_job = new \PhpTaskDaemon\Task\Job\BaseClass('test');
		$inputArray1 = array('inputVar1');
		$inputArray2 = array('inputVar1', 'inputVar2');
		$this->assertEquals(array(), $this->_job->getInputKeys());
		
		$this->_job->setInputKeys($inputArray1);
		$this->assertEquals($inputArray1, $this->_job->getInputKeys());
		
		$this->_job->setInputKeys($inputArray2);
		$this->assertEquals($inputArray2, $this->_job->getInputKeys());
	}

	public function testSetInput() {
		$input1 = array('inputVar1' => '1234');
		$input2 = array('inputVar1' => '1234', 'inputVar2' => '5678');
		$this->_job = new \PhpTaskDaemon\Task\Job\BaseClass('test');
		$this->_job->setInputKeys(array('inputVar1', 'inputVar2'));

		$this->assertEquals(0, sizeof($this->_job->getInput()));
		
		$this->_job->setInput($input1);
		$this->assertEquals(1, sizeof($this->_job->getInput()));
		$this->assertEquals('1234', $this->_job->getInputVar('inputVar1'));
		$this->assertEquals(1, sizeof($this->_job->getInput()));
		
		$this->_job->setInput($input2);
		$this->assertEquals(2, sizeof($this->_job->getInput()));
		$this->assertEquals('1234', $this->_job->getInputVar('inputVar1'));
		$this->assertEquals('5678', $this->_job->getInputVar('inputVar2'));
	}

	public function testSetInputVarCorrectKeys() {
		$this->_job = new \PhpTaskDaemon\Task\Job\BaseClass('test');
		$this->_job->setInputKeys(array('inputVar1', 'inputVar2'));
		$this->_job->setInputVar('inputVar1', '9012');
		
		$this->assertEquals(1, sizeof($this->_job->getInput()));
		$this->assertEquals('9012', $this->_job->getInputVar('inputVar1'));
				
		$this->_job->setInputVar('inputVar2', '3456');
		$this->assertEquals('3456', $this->_job->getInputVar('inputVar2'));
		$this->assertEquals(2, sizeof($this->_job->getInput()));
	}
	public function testSetInputVarInCorrectKeys() {
		$this->_job = new \PhpTaskDaemon\Task\Job\BaseClass('test');
		$this->_job->setInputKeys(array('inputVar1', 'inputVar2'));
		$this->_job->setInputVar('inputVar1', '9012');
		
		$this->assertEquals(1, sizeof($this->_job->getInput()));
		$this->assertEquals('9012', $this->_job->getInputVar('inputVar1'));
				
		$this->_job->setInputVar('testVar3', '3456');
		$this->assertEquals(null, $this->_job->getInputVar('testVar3'));
		$this->assertEquals(1, sizeof($this->_job->getInput()));
	}

	public function testSetOutput() {
		$output1 = array('inputVar1' => '4321');
		$output2 = array('inputVar1' => '4321', 'inputVar2' => '8765');
		$this->_job = new \PhpTaskDaemon\Task\Job\BaseClass('test');
		$this->assertEquals(0, sizeof($this->_job->getOutput()));
		
		$this->_job->setOutput($output1);
		$this->assertEquals(1, sizeof($this->_job->getOutput()));
		$this->assertEquals('4321', $this->_job->getOutputVar('inputVar1'));
		$this->assertEquals(1, sizeof($this->_job->getOutput()));
		
		$this->_job->setOutput($output2);
		$this->assertEquals(2, sizeof($this->_job->getOutput()));
		$this->assertEquals('4321', $this->_job->getOutputVar('inputVar1'));
		$this->assertEquals('8765', $this->_job->getOutputVar('inputVar2'));
	}

	public function testSetOutputVarCorrectKeys() {
		$this->_job = new \PhpTaskDaemon\Task\Job\BaseClass('test');

		$this->_job->setOutputVar('outputVar1', '9012');		
		$this->assertEquals(1, sizeof($this->_job->getOutput()));
		$this->assertEquals('9012', $this->_job->getOutputVar('outputVar1'));
				
		$this->_job->setOutputVar('outputVar2', '3456');
		$this->assertEquals(2, sizeof($this->_job->getOutput()));
		$this->assertEquals('3456', $this->_job->getOutputVar('outputVar2'));
	}
	public function testCheckInput() {
		$this->_job = new \PhpTaskDaemon\Task\Job\BaseClass('test');
		$this->_job->setInputKeys(array('inputVar1', 'inputVar2'));
//		$this->assertTrue($this->_job->checkInput());
//
//		$this->_job->setInputVar('inputVar1', '1234');		
//		$this->assertEquals(1, sizeof($this->_job->getInput()));
//		$this->assertTrue($this->_job->checkInput());
	}
	
}
