<?php

namespace PhpTaskDaemon\Daemon;

class Config {

	protected static $_instance = null;

	/** 
	 * Protected constructor for singleton pattern
	 */
	protected function __construct() {
	}
	
	public function get() {
        if (self::$_instance) {
            return self::$_instance;
        }

        return self::$_instance = self::createInstance();
	}
}