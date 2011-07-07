<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon;

use \PhpTaskDaemon\Daemon\Logger;

/**
 * The config component uses a Zend_Config object to store and retrieve
 * configuration options. For task specific configuration options the 
 * PhpTaskDaemon Config object first checks the task specific configuration
 * section before returning a daemon-wide configuration option or the library
 * default.
 *
 */
class Config {
	protected static $_instance = null;
	
	/**
	 * Zend_Config object instance
	 * @var \Zend_Config
	 */
    protected $_config = null;
	
	/** 
	 * Protected constructor for singleton pattern
	 */
	protected function __construct($configFiles = array()) {
		$this->_initConfig($configFiles);
	}

	protected function _initConfig($configFiles) {
        // Add default configuration
        array_unshift($configFiles, realpath(\APPLICATION_PATH . '/../etc/app.ini'));
        array_unshift($configFiles, realpath(\APPLICATION_PATH . '/../etc/daemon.ini'));
        
        foreach($configFiles as $configFile) {
        	Logger::get()->log("Trying config file: " . $configFile, \Zend_Log::DEBUG);
            if (!file_exists($configFile)) {
                Logger::get()->log("Config file does not exists: " . $configFile, \Zend_Log::ERR);
                continue;
            }

            if (!is_a($this->_config, '\Zend_Config')) {
                // First config
                $this->_config = new \Zend_Config_Ini(
                    $configFile,
                    \APPLICATION_ENV,
                    array('allowModifications' => true)
                );
            } else {
                // Merge config file
                $this->_config->merge(
                    new \Zend_Config_Ini(
                        $configFile, 
                        \APPLICATION_ENV
                    )
                );
            }
            Logger::get()->log("Loaded config file: " . $configFile, \Zend_Log::DEBUG);
        }
        $this->_config->setReadonly();
	}

	/**
	 * Singleton getter
	 * @return \PhpTaskDaemon\Daemon\Config
	 */
	public function get($configFiles = array()) {
        if (!self::$_instance) {
        	Logger::get()->log("Creating new config object", \Zend_Log::DEBUG);
            self::$_instance = new self($configFiles);
        }

        return self::$_instance;
	}


	/**
	 * Returns the configuration instance
	 * @returns Zend_Config
	 */
	public function getConfig() {
		return $this->_config;
	}


	/**
	 * Sets the configuration instance
	 * @param Zend_Config $config
	 */
	public function setConfig($config) {
		$this->_config = $config;
	}


    /**
     * Returns a configuration option. If the taskName is specified, then it
     * first looks at the task specific configuration option. If not set the
     * default will be returned.
     * @param string $option
     * @param string $taskName
     * @param null|string
     */
	public function getOption($option, $taskName = null) {
		$value = null;
		if (!is_null($taskName)) {
			$value = $this->getOptionByTaskConfig($option, $taskName);
		}
		if (is_null($option)) {
			$value = $this->getOptionByDaemonConfig($option);
		}
		return $value;
	}


	/**
	 * Returns a daemon and/or system wide configuration option.
	 * @param string $option
	 * @return null|string
	 */
	public function getOptionByDaemonConfig($option) {
		return $this->_config->daemon->get($option);
	}


	/**
	 * Returns a task specific configuration option
	 * @param string $option
	 * @param string $taskName
	 * @return null|string
	 */
    public function getOptionByTaskConfig($option, $taskName) {
        $option = null;
        return $this->_config->get($taskName)->getOption($option);
    }

}