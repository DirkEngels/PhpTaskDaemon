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
 * @group PhpTaskDaemon-Task-Job-Data
 */

namespace PhpTaskDaemon\Task\Data\Job;

class BaseClassTest extends \PHPUnit_Framework_Testcase {

    /**
     * @var \PhpTaskDaemon\Task\Job\Data\AbstractClass
     */
    protected $_data;

    /**
     * @var array
     */
    protected $_mockData;

    protected function setUp() {
        $this->_data = new \PhpTaskDaemon\Task\Job\Data\BaseClass();
        $this->_mockData = array(
            'key1' => 'value1',
            'key2' => 'value2',
            'array1' => array(
                'subkey1' => 'subvalue1',
                'subkey2' => 'subvalue2',
            ),
            'array2' => array(
                'subkey3' => 'subvalue3',
            )
        );
        
    }

    protected function tearDown() {
        unset($this->_data);
    }

    public function testConstructorNoArguments() {
        $this->assertInstanceOf('\PhpTaskDaemon\Task\Job\Data\AbstractClass', $this->_data);
        $this->assertInternalType('array', $this->_data->get());
        $this->assertEquals(0, sizeof($this->_data->get()));
        $this->assertEquals(0, sizeof($this->_data->getKeys()));
    }
    public function testInitWithDataArguments() {
        $this->_data = new \PhpTaskDaemon\Task\Job\Data\BaseClass($this->_mockData);
        $this->assertInstanceOf('\PhpTaskDaemon\Task\Job\Data\AbstractClass', $this->_data);
        $this->assertInternalType('array', $this->_data->get());
        $this->assertEquals(4, sizeof($this->_data->get()));
        $this->assertEquals(4, sizeof($this->_data->getKeys()));
    }

    public function testSetVarForce() {
        $this->assertEquals(0, sizeof($this->_data->get()));
        $this->assertEquals(0, sizeof($this->_data->getKeys()));

        $this->assertTrue($this->_data->setVar('foo', 'bar', true));

        $this->assertEquals(1, sizeof($this->_data->get()));
        $this->assertEquals(1, sizeof($this->_data->getKeys()));
        $this->assertEquals('bar', $this->_data->getVar('foo'));
    }

    public function testSetVarInitialized() {
        $this->_data = new \PhpTaskDaemon\Task\Job\Data\BaseClass($this->_mockData);

        $this->assertEquals(4, sizeof($this->_data->get()));
        $this->assertEquals(4, sizeof($this->_data->getKeys()));
        $this->assertEquals('value1', $this->_data->getVar('key1'));

        $this->assertTrue($this->_data->setVar('key1', 'newvalue'));

        $this->assertEquals(4, sizeof($this->_data->get()));
        $this->assertEquals(4, sizeof($this->_data->getKeys()));
        $this->assertEquals('newvalue', $this->_data->getVar('key1'));
    }

    public function testSetVarDoesNotExists() {
        $this->assertEquals(0, sizeof($this->_data->get()));
        $this->assertEquals(0, sizeof($this->_data->getKeys()));

        $this->assertFalse($this->_data->setVar('foo', 'bar'));

        $this->assertEquals(0, sizeof($this->_data->get()));
        $this->assertEquals(0, sizeof($this->_data->getKeys()));
        $this->assertNull($this->_data->getVar('foo'));
    }

    public function testSet () {
        $this->assertEquals(0, sizeof($this->_data->get()));
        $this->assertEquals(0, sizeof($this->_data->getKeys()));

        $this->assertTrue($this->_data->set($this->_mockData));

        $this->assertEquals(4, sizeof($this->_data->get()));
        $this->assertEquals(4, sizeof($this->_data->getKeys()));
        $this->assertEquals('value1', $this->_data->getVar('key1'));
        $this->assertEquals('value2', $this->_data->getVar('key2'));

        $this->assertInternalType('array', $this->_data->getVar('array1'));
        $this->assertNull($this->_data->getVar('array_does_not_exists'));
    }

    public function testValidate() {
        $this->assertTrue($this->_data->validate());
    }

}

