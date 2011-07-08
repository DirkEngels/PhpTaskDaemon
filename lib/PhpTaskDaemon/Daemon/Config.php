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
        array_unshift($configFiles, realpath(\APPLICATION_PATH . '/../etc/defaults.ini'));
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
            Logger::get()->log("Loaded config file: " . $configFile, \Zend_Log::INFO);
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
            $value = $this->getTaskOption($option, $taskName);
            if (isset($value)) {
                return $value;
            }
        }

        $value = $this->getDaemonOption($option);
        if (isset($value)) {
            return $value;
        }

        Logger::get()->log('Config option not declared: ' . $option, \Zend_Log::CRIT);
        throw new \Exception('Config option not declared!');
    }


    /**
     * Returns the Ret
     * @param $option
     * @param $taskName
     */
    public function getDaemonOption($option) {
        Logger::get()->log('Trying daemon config option: ' . $option, \Zend_Log::DEBUG);
        return $this->getRecursiveKey($option);
    }


    public function getTaskOption($option, $taskName = null) {
    	if (!is_null($taskName)) {
	    	try {
	            $value = $this->getTaskSpecificOption($option, $taskName);
	    	} catch (\Exception $e) {
	            \PhpTaskDaemon\Daemon\Logger::get()->log($e->getMessage(), \Zend_Log::DEBUG);
	    	}
    	}

        if (!isset($value)) {
            $value = $this->getTaskDefaultOption($option);
        }

        return $value;
    }


    /**
     * Returns the default task configuration option
     * @param $option
     */
    public function getTaskDefaultOption($option) {
        $value = null;
        Logger::get()->log('Trying default config option: tasks.defaults.' . $option, \Zend_Log::DEBUG);
        try {
            $value = $this->getRecursiveKey('tasks.defaults.' . $option);
        } catch (\Exception $e) {
            Logger::get()->log('Failed loading default config option: ' . $option, \Zend_Log::DEBUG);
        }
        return $value;
    }


    /**
     * Returns the Ret
     * @param $option
     * @param $taskName
     */
    public function getTaskSpecificOption($option, $taskName) {
        Logger::get()->log('Trying task config option: tasks.' . $this->_prepareString($taskName) . '.' . $option, \Zend_Log::DEBUG);
        return $this->getRecursiveKey('tasks.' . $taskName . '.' . $option);
    }


    /**
     * Recursively check if a config value exists untill the required nesting 
     * level has been reached.
     * @param $keyString
     */
    public function getRecursiveKey($keyString) {
        $keyString = $this->_prepareString($keyString);
        $value = null;
        $keyPieces = explode('.', $keyString);
        $config = $this->_config;
        foreach ($keyPieces as $keyPiece) {
            $config = $config->get($keyPiece);
            if (!isset($config)) {
                throw new \Exception('Config option not found');
            }
        }

        return $config;
    }

    protected function _prepareString($string) {
        return strtolower( str_replace('/', '.', $string) );
    }
}