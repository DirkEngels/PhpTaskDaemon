<?php

/**
 * @package PhpTaskDaemon
 * @subpackage Daemon\Config
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 *
 * @group PhpTaskDaemon
 * @group PhpTaskDaemon-Daemon
 * @group PhpTaskDaemon-Daemon-Config
 */


namespace PhpTaskDaemon\Daemon\Pid;

class ConfigTest extends \PHPUnit_Framework_TestCase {
    protected $_config;

    protected function setUp() {
    }

    protected function tearDown() {
    }

    public function testConstructInstanceDefault() {
        $this->_config = \PhpTaskDaemon\Daemon\Config::get();

        $this->assertInstanceOf('Zend_Config', $this->_config->getConfig());
        $this->assertInternalType('array', $this->_config->getLoadedConfigFiles());
        $this->assertEquals(3, count($this->_config->getLoadedConfigFiles()));
    }

    public function testConstructInstanceSingleFile() {
        $this->_config = \PhpTaskDaemon\Daemon\Config::get(array(
            realpath(__DIR__ . '/_data/daemon.ini'),
        ));

        $this->assertInstanceOf('Zend_Config', $this->_config->getConfig());
        $this->assertInternalType('array', $this->_config->getLoadedConfigFiles());
        $this->assertEquals(1, count($this->_config->getLoadedConfigFiles()));
    }

    /**
     * @expectedException Exception
     */
    public function testConstructInstanceSingleFileNonExisting() {
        $this->_config = \PhpTaskDaemon\Daemon\Config::get(array(
            realpath(__DIR__ . '/_data/blaat.ini'),
        ));

        $this->assertInstanceOf('Zend_Config', $this->_config->getConfig());
        $this->assertInternalType('array', $this->_config->getLoadedConfigFiles());
        $this->assertEquals(1, count($this->_config->getLoadedConfigFiles()));
    }

    public function testConstructInstanceMultipleFiles() {
        $this->_config = \PhpTaskDaemon\Daemon\Config::get(array(
            realpath(__DIR__ . '/_data/daemon.ini'),
            realpath(__DIR__ . '/_data/app.ini'),
        ));

        $this->assertInstanceOf('Zend_Config', $this->_config->getConfig());
        $this->assertInternalType('array', $this->_config->getLoadedConfigFiles());
        $this->assertEquals(2, count($this->_config->getLoadedConfigFiles()));
    }

    public function testConstructInstanceMultipleFilesOneNonExisting() {
        $this->_config = \PhpTaskDaemon\Daemon\Config::get(array(
            realpath(__DIR__ . '/_data/daemon.ini'),
            realpath(__DIR__ . '/_data/app.ini'),
            realpath(__DIR__ . '/_data/blaat.ini'),
        ));

        $this->assertInstanceOf('Zend_Config', $this->_config->getConfig());
        $this->assertInternalType('array', $this->_config->getLoadedConfigFiles());
        $this->assertEquals(2, count($this->_config->getLoadedConfigFiles()));
    }

    public function testSetConfig() {
        $this->_config = \PhpTaskDaemon\Daemon\Config::get();

        $config = new \Zend_Config(array());
        $this->assertNotEquals($config, $this->_config->getConfig());

        $this->assertEquals(
            $this->_config,
            $this->_config->setConfig($config)
        );
        $this->assertEquals($config, $this->_config->getConfig());
    }

    public function testGetOption() {
        $this->_config = \PhpTaskDaemon\Daemon\Config::get(array(
            realpath(__DIR__ . '/_data/daemon.ini'),
        ));

        $this->assertInternalType('array', $this->_config->getOption('daemon.log.level'));
        $this->assertEquals(
            'a:2:{i:0;s:8:"fallback";i:1;s:1:"3";}', 
            serialize($this->_config->getOption('daemon.log.level'))
        );
    }

    public function testGetValue() {
        $this->_config = \PhpTaskDaemon\Daemon\Config::get(array(
            realpath(__DIR__ . '/_data/daemon.ini'),
        ));

        $this->assertInternalType('string', $this->_config->getOptionValue('daemon.log.level'));
        $this->assertEquals(3, $this->_config->getOptionValue('daemon.log.level'));
    }

    public function testGetSource() {
        $this->_config = \PhpTaskDaemon\Daemon\Config::get(array(
            realpath(__DIR__ . '/_data/daemon.ini'),
        ));

        $this->assertInternalType('string', $this->_config->getOptionSource('daemon.log.level'));
        $this->assertEquals('fallback', $this->_config->getOptionSource('daemon.log.level'));
    }

}