<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon;

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
    protected $_config = null;
	
	/** 
	 * Protected constructor for singleton pattern
	 */
	protected function __construct() {
	}


	/**
	 * Singleton getter
	 */
	public function get() {
        if (self::$_instance) {
            return self::$_instance;
        }

        return self::$_instance = self::createInstance();
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
		$option = null;
		if (!is_null($taskName)) {
			$option = $this->getTaskSetting($option, $taskName);
		}
		if (is_null($option)) {
			$option = $this->getDaemonSetting($option);
		}
		return $option;
	}


	/**
	 * Returns a daemon and/or system wide configuration option.
	 * @param string $option
	 * @return null|string
	 */
	public function getDaemonOption($option) {
		return $this->_config->daemon->get($option);
	}


	/**
	 * Returns a task specific configuration option
	 * @param string $option
	 * @param string $taskName
	 * @return null|string
	 */
    public function getTaskOption($option, $taskName) {
        $option = null;
        $taskConfig = $this->_config->get($taskName);
        return $taskConfig->get($option);
    }

}