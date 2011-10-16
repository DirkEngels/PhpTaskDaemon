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
    protected static $_instance = NULL;

    /**
     * Zend_Config object instance
     * @var \Zend_Config
     */
    protected $_config = NULL;

    /**
     * Loaded configurations files
     * @var array
     */
    protected $_files = array();


    /** 
     * Protected constructor for singleton pattern
     */
    protected function __construct($configFiles = array()) {
        if (count($configFiles) == 0) {
            // Add default configuration
            array_unshift($configFiles, realpath(\APPLICATION_PATH . '/etc/app.ini'));
            array_unshift($configFiles, realpath(\APPLICATION_PATH . '/etc/defaults.ini'));
            array_unshift($configFiles, realpath(\APPLICATION_PATH . '/etc/daemon.ini'));
        }

        $this->_initConfig($configFiles);
    }


    /**
     * Singleton getter
     * @return \PhpTaskDaemon\Daemon\Config
     */
    public function get($configFiles = array()) {
        if (count($configFiles) > 0) {
            self::$_instance = NULL;
        }

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
        return $this;
    }


    /**
     * Returns the loaded configurations files.
     * @return array
     */
    public function getLoadedConfigFiles() {
        return $this->_files;
    }

    /**
     * Returns a configuration option. If the taskName is specified, then it
     * first looks at the task specific configuration option. If not set the
     * default will be returned.
     * @param string $option
     * @param string $taskName
     * @param NULL|string
     */
    public function getOption($option, $taskName = NULL) {
        $value = NULL;
        $source = NULL;

        // Task option
        if (!is_null($taskName)) {
            try {
                $value = $this->_getRecursiveKey('tasks.' . $taskName . '.' . $option);
                $source = 'task';
            } catch (\Exception $e) {
                Logger::get()->log('TASK SPECIFIC ' . $e->getMessage(), \Zend_Log::DEBUG);
            }
        }

        if (is_null($source)) {
            try {
                $value = $this->_getRecursiveKey('tasks.defaults.' . $option);
                $source = 'default';
            } catch (\Exception $e) {
                Logger::get()->log('TASK DEFAULT ' . $e->getMessage(), \Zend_Log::DEBUG);
            }
        }

        // Daemon option
        if (is_null($source)) {
            try {
                $value = $this->_getRecursiveKey('daemon.' . $option);
                $source = 'daemon';
            } catch (\Exception $e) {
                Logger::get()->log('DAEMON ' . $e->getMessage(), \Zend_Log::DEBUG);
            }
        }

        // Fallback
        if (is_null($source)) {
            try {
                $value = $this->_getRecursiveKey($option);
                $source = 'fallback';
            } catch (\Exception $e) {
                Logger::get()->log('FALLBACK ' . $e->getMessage(), \Zend_Log::DEBUG);
            }
        }

        Logger::get()->log('Config option result: ' . $option . ' => ' . $value . ' (' . $source . ')', \Zend_Log::ERR);
        $out = array($source, $value);
        return $out;
    }


    /**
     * Returns the source of a configuration options
     * @param $option
     * @param $taskName
     * @return string
     */
    public function getOptionSource($option, $taskName = NULL) {
        list($source, $value) = $this->getOption($option, $taskName);
        return $source;
    }


    /**
     * Returns the value of a configuration options
     * @param $option
     * @param $taskName
     * @return string
     */
    public function getOptionValue($option, $taskName = NULL) {
        list($source, $value) = $this->getOption($option, $taskName);
        return $value;
    }


    /**
     * Initializes the configuration by loading one or more (default)
     * configuration files
     * @param array $configFiles
     */
    protected function _initConfig($configFiles) {
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
                    array('allowModifications' => TRUE)
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
            // Register configurations file
            array_push($this->_files, $configFile);
            Logger::get()->log("Loaded config file: " . $configFile, \Zend_Log::INFO);
        }

        // At least one configuration file must be loaded.
        if (count($this->_files) == 0) {
            throw new \Exception('No configuration files found!');
        }

        $this->_config->setReadonly();
    }


    /**
     * Recursively check if a config value exists untill the required nesting 
     * level has been reached.
     * @param $keyString
     */
    protected function _getRecursiveKey($keyString) {
        $keyString = $this->_prepareString($keyString);
        $keyPieces = explode('.', $keyString);

        $configArray = Config::get()->getConfig()->toArray();
        $value = $configArray;
        foreach($keyPieces as $keyPiece) {
            if (isset($value[$keyPiece])) {
                $value = $value[$keyPiece];
            } else {
                throw new \Exception('Config option does not exists: ' . $keyString);
            }
        }
        return $value;
    }


    /**
     * Prepares the string by replacing slashes with dots and makes the string
     * lowercase. 
     * @param string $string
     * @return string
     */
    protected function _prepareString($string) {
        return strtolower( str_replace('/', '.', $string) );
    }

}