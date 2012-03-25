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

    // Config
    const CONFIG_FILE_APP = '/etc/app.ini';
    const CONFIG_FILE_DEFAULTS = '/etc/defaults.ini';
    const CONFIG_FILE_DAEMON = '/etc/daemon.ini';

    const CONFIG_SOURCE_TASK     = 'task';
    const CONFIG_SOURCE_DAEMON   = 'daemon';
    const CONFIG_SOURCE_DEFAULT  = 'default';
    const CONFIG_SOURCE_FALLBACK = 'fallback';

    // Messages
    const MSG_CONFIG_INSTANTIATED = 'Config instantiated';
    const MSG_CONFIG_TRYING = 'Config file to load: %s';
    const MSG_CONFIG_LOADED = 'Config file loaded: %s';
    const MSG_CONFIG_NOTFOUND = 'Config file not found: %s';

    const MSG_OPTION_EXCEPTION = 'Config option exception in %s: %s => %s';
    const MSG_OPTION_VALUE = 'Config option result: %s => %s (%s)';

    protected static $_instance = NULL;

    /**
     * Zend_Config object instance.
     * 
     * @var \Zend_Config
     */
    protected $_config = NULL;

    /**
     * Loaded configurations files.
     * 
     * @var array
     */
    protected $_files = array();


    /** 
     * Protected constructor for singleton pattern.
     */
    protected function __construct( $configFiles = array() ) {
        if (count($configFiles) == 0) {
            // Add default configuration
            array_unshift($configFiles, realpath(\APPLICATION_PATH . self::CONFIG_FILE_APP ) );
            array_unshift($configFiles, realpath(\APPLICATION_PATH . self::CONFIG_FILE_DEFAULTS ) );
            array_unshift($configFiles, realpath(\APPLICATION_PATH . self::CONFIG_FILE_DAEMON ) );
        }

        $this->_initConfig($configFiles);
    }


    /**
     * Singleton getter.
     * 
     * @return \PhpTaskDaemon\Daemon\Config
     */
    public static function get($configFiles = array()) {
        // Reset Config instance
        if ( count( $configFiles ) > 0) {
            self::$_instance = NULL;
        }

        // Create new Config instance
        if ( ! self::$_instance ) {
            Logger::log( self::MSG_CONFIG_INSTANTIATED, \Zend_Log::DEBUG );
            self::$_instance = new self( $configFiles );
        }

        return self::$_instance;
    }


    /**
     * Returns the configuration instance.
     * 
     * @returns Zend_Config
     */
    public function getConfig() {
        return $this->_config;
    }


    /**
     * Sets the configuration instance.
     * 
     * @param Zend_Config $config
     */
    public function setConfig($config) {
        $this->_config = $config;
        return $this;
    }


    /**
     * Returns the loaded configurations files.
     * 
     * @return array
     */
    public function getLoadedConfigFiles() {
        return $this->_files;
    }


    /**
     * Returns a configuration option. If the taskName is specified, then it
     * first looks at the task specific configuration option. If not set the
     * default will be returned.
     * 
     * @param string $option
     * @param string $taskName
     * @param NULL|string
     */
    public function getOption($option, $taskName = NULL) {
        $value = NULL;
        $source = NULL;

        // Task option
        if ( ! is_null( $taskName ) ) {
            try {
                $value = $this->_getRecursiveKey( 'tasks.' . $taskName . '.' . $option );
                $source = self::CONFIG_SOURCE_TASK;
            } catch (\Exception $e) {
                Logger::log('TASK SPECIFIC ' . $e->getMessage(), \Zend_Log::DEBUG);
            }
        }

        // Daemon option
        if (is_null($source)) {
            try {
                $value = $this->_getRecursiveKey('daemon.' . $option);
                $source = self::CONFIG_SOURCE_DAEMON;
            } catch (\Exception $e) {
                Logger::log('DAEMON ' . $e->getMessage(), \Zend_Log::DEBUG);
            }
        }

        // Defaults
        if (is_null($source)) {
            try {
                $value = $this->_getRecursiveKey('tasks.defaults.' . $option);
                $source = self::CONFIG_SOURCE_DEFAULT;
            } catch (\Exception $e) {
                Logger::log('TASK DEFAULT ' . $e->getMessage(), \Zend_Log::DEBUG);
            }
        }

        // Fallback
        if (is_null($source)) {
            try {
                $value = $this->_getRecursiveKey($option);
                $source = self::CONFIG_SOURCE_FALLBACK;
            } catch (\Exception $e) {
                Logger::log('FALLBACK ' . $e->getMessage(), \Zend_Log::DEBUG);
            }
        }

        $msg = sprintf( self::MSG_OPTION_VALUE, $option, $value, $source );
        Logger::log( $msg, \Zend_Log::DEBUG);
        $out = array($source, $value);
        return $out;
    }


    /**
     * Returns the source of a configuration options.
     * 
     * @param $option
     * @param $taskName
     * @return string
     */
    public function getOptionSource($option, $taskName = NULL) {
        list($source, $value) = $this->getOption($option, $taskName);
        return $source;
    }


    /**
     * Returns the value of a configuration options.
     * 
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
     * configuration files.
     * 
     * @param array $configFiles Optional configuration files. 
     * @exception \PhpTaskDaemon\Exception\FileNotFound No files found.
     */
    protected function _initConfig( $configFiles = array() ) {
        foreach( $configFiles as $configFile ) {
            $msg = sprintf( self::MSG_CONFIG_TRYING, $configFile);
            Logger::log( $msg, \Zend_Log::DEBUG );

            // Config file exists.
            if (!file_exists($configFile)) {
                $msg = sprintf( self::MSG_CONFIG_NOTFOUND, $configFile );
                Logger::log( $msg, \Zend_Log::ERR );
                continue;
            }

            if ( ! is_a( $this->_config, '\Zend_Config' ) ) {
                // First config file.
                $this->_config = new \Zend_Config_Ini(
                    $configFile,
                    \APPLICATION_ENV,
                    array( 'allowModifications' => TRUE )
                );

            } else {
                // Merge config file.
                $this->_config->merge(
                    new \Zend_Config_Ini(
                        $configFile, 
                        \APPLICATION_ENV
                    )
                );
            }

            // Register configurations file
            array_push($this->_files, $configFile);
            $msg = sprintf( self::MSG_CONFIG_LOADED, $configFile );
            Logger::log( $msg, \Zend_Log::INFO);
        }

        // At least one configuration file must be loaded.
        if (count($this->_files) == 0) {
            throw new \PhpTaskDaemon\Exception\FileNotFound('No configuration files found!');
        }

        $this->_config->setReadonly();
        return TRUE;
    }


    /**
     * Recursively check if a config value exists untill the required nesting 
     * level has been reached.
     * 
     * @param $keyString Strips the config key in order to find a value.
     */
    protected function _getRecursiveKey($keyString) {
        // Format config strings, because it is a framework.
        $keyString = $this->_prepareString($keyString);
        $configArray = explode('.', $keyString);

        $value= $configArray;
        $configArray = Config::get()->getConfig()->toArray();
        foreach( $configArray as $keyPiece ) {
            if ( ! in_array( $keyPiece, $value ) ) {
                throw new \Exception('Config option does not exists: ' . $keyString);
            }
            $value = $value[$keyPiece];
        }
        return $value;
    }


    /**
     * Prepares the string by replacing slashes with dots and makes the string
     * lowercase.
     * 
     * @param string $string
     * @return string Formats a config key.s
     */
    protected function _prepareString( $string ) {
        $string = str_replace( '/', '.', $string );
        $string = strtolower( $string );
        return $string;
    }

}
