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
            Logger::get()->log('Trying task config option: ' . $taskName . '.' . $option, \Zend_Log::DEBUG);
            $value =$this->getConfigKey($taskName . '.' . $option);
            if (isset($value)) {
                return $value;
            }
        }

        Logger::get()->log('Trying daemon config option: daemon.' . $option, \Zend_Log::DEBUG);
        $value = $this->getConfigKey('daemon.' . $option);
        if (isset($value)) {
            return $value;
        }

        Logger::get()->log('Trying task config option: ' . $option, \Zend_Log::CRIT);
        throw new \Exception('Config option not declared!');
    }


    /**
     * Recursively check if a config value exists untill the required nesting 
     * level has been reached.
     * @param $keyString
     */
    public function getConfigKey($keyString) {
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

}