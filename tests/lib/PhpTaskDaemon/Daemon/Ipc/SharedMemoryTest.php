<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Ipc
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Daemon
 * @group PhpTaskDaemon-Daemon-Ipc
 * @group PhpTaskDaemon-Daemon-Ipc-SharedMemory
 */

namespace PhpTaskDaemon\Daemon\Ipc;

class SharedMemoryTest extends \PHPUnit_Framework_TestCase {
    protected $_sharedMemory;
    
    protected function setUp() {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

        $semaphore = __DIR__ . '/_data/id.shm';
        $this->_sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory($semaphore);
    }
    protected function tearDown() {
            if ($this->_sharedMemory instanceof 'PhpTaskDaemon\Daemon\Ipc\SharedMemory') {
                $this->_sharedMemory->remove();
            }
    }
    
    public function testConstructorAbsoluteId() {
        $this->assertEquals(0, sizeof($this->_sharedMemory->get()));
        $this->assertEquals('a:0:{}', serialize($this->_sharedMemory->get()));
    }
    public function testGetKeys() {
        $this->assertEquals(0, sizeof($this->_sharedMemory->getKeys()));
        $this->assertInternalType('array', $this->_sharedMemory->getKeys());
    }
    public function testConstructorRelativeId() {
        $this->_sharedMemory->remove();
        
        $semaphore = __DIR__ . '/_data/id.shm';
        $this->_sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory('relative');
        
        $this->assertEquals(0, sizeof($this->_sharedMemory->get()));
        $this->assertEquals('a:0:{}', serialize($this->_sharedMemory->get()));
    }
    
    public function testSetVarNew() {
        $semaphore = __DIR__ . '/_data/id.shm';
        $this->_sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory($semaphore);
        $this->_sharedMemory->setVar('testvar1', '123456');
        $this->assertEquals(1, sizeof($this->_sharedMemory->get()));
        $this->assertEquals('123456', $this->_sharedMemory->getVar('testvar1'));
    }

    public function testSetVarUpdate() {
        $semaphore = __DIR__ . '/_data/id.shm';
        $this->_sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory($semaphore);
        $this->_sharedMemory->setVar('testvar1', '123456');
        $this->assertEquals(1, sizeof($this->_sharedMemory->get()));
        $this->assertEquals('123456', $this->_sharedMemory->getVar('testvar1'));
        
        $this->_sharedMemory->setVar('testvar1', '654321');
        $this->assertEquals(1, sizeof($this->_sharedMemory->get()));
        $this->assertEquals('654321', $this->_sharedMemory->getVar('testvar1'));
    }
    public function testSetVarMultiple() {
        $semaphore = __DIR__ . '/_data/id.shm';
        $this->_sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory($semaphore);
        $this->_sharedMemory->setVar('testvar1', '123456');
        $this->assertEquals(1, sizeof($this->_sharedMemory->get()));
        $this->assertEquals('123456', $this->_sharedMemory->getVar('testvar1'));
        
        $this->_sharedMemory->setVar('testvar2', '654321');
        $this->assertEquals(2, sizeof($this->_sharedMemory->get()));
        $this->assertEquals('654321', $this->_sharedMemory->getVar('testvar2'));
    }
    public function testIncrementVarNew() {
        $semaphore = __DIR__ . '/_data/id.shm';
        $this->_sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory($semaphore);
        $this->assertFalse($this->_sharedMemory->getVar('testvar1'));
        
        $this->_sharedMemory->incrementVar('testvar1');
        $this->assertEquals('1', $this->_sharedMemory->getVar('testvar1'));
    }
    public function testIncrementVarUpdate() {
        $semaphore = __DIR__ . '/_data/id.shm';
        $this->_sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory($semaphore);
        $this->_sharedMemory->setVar('testvar1', '123456');
        $this->assertEquals(1, sizeof($this->_sharedMemory->get()));
        $this->assertEquals('123456', $this->_sharedMemory->getVar('testvar1'));
        
        $this->_sharedMemory->incrementVar('testvar1');
        $this->assertEquals('123457', $this->_sharedMemory->getVar('testvar1'));
    }
    public function testDecrementVarNew() {
        $semaphore = __DIR__ . '/_data/id.shm';
        $this->_sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory($semaphore);
        $this->assertFalse($this->_sharedMemory->getVar('testvar1'));
        
        $this->_sharedMemory->decrementVar('testvar1');
        $this->assertEquals('0', $this->_sharedMemory->getVar('testvar1'));
    }
    public function testDecrementVarUpdate() {
        $semaphore = __DIR__ . '/_data/id.shm';
        $this->_sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory($semaphore);
        $this->_sharedMemory->setVar('testvar1', '123456');
        $this->assertEquals(1, sizeof($this->_sharedMemory->get()));
        $this->assertEquals('123456', $this->_sharedMemory->getVar('testvar1'));
        
        $this->_sharedMemory->decrementVar('testvar1');
        $this->assertEquals('123455', $this->_sharedMemory->getVar('testvar1'));
    }
    public function testRemoveVar() {
        $semaphore = __DIR__ . '/_data/id.shm';
        $this->_sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory($semaphore);
        $this->_sharedMemory->setVar('testvar1', '123456');
        $this->assertEquals(1, sizeof($this->_sharedMemory->get()));
        $this->assertEquals('123456', $this->_sharedMemory->getVar('testvar1'));
        
        $this->_sharedMemory->setVar('testvar2', '654321');
        $this->assertEquals(2, sizeof($this->_sharedMemory->get()));
        $this->assertEquals('654321', $this->_sharedMemory->getVar('testvar2'));
        
        $this->_sharedMemory->removeVar('testvar2');
        $this->assertEquals(1, sizeof($this->_sharedMemory->get()));
        $this->assertEquals('123456', $this->_sharedMemory->getVar('testvar1'));
        
    }
    public function testRemove() {
        $semaphore = __DIR__ . '/_data/id.shm';
        $this->_sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory($semaphore);
        $this->_sharedMemory->setVar('testvar1', '123456');
        $this->assertEquals(1, sizeof($this->_sharedMemory->get()));
        $this->assertEquals('123456', $this->_sharedMemory->getVar('testvar1'));
        
        $this->_sharedMemory->setVar('testvar2', '654321');
        $this->assertEquals(2, sizeof($this->_sharedMemory->get()));
        $this->assertEquals('654321', $this->_sharedMemory->getVar('testvar2'));
        
        $this->_sharedMemory->remove();
        $this->_sharedMemory = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory($semaphore);
    }

}
