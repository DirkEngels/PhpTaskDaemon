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
 * The logger component uses a Zend_Log object to log messages.
 *
 */
class Logger {

    // Config
    const CONFIG_DAEMON_LOG_LEVEL = 'daemon.log.level';

    protected static $_instance = NULL;


    /** 
     * Protected constructor for singleton pattern.
     */
    protected function __construct() {
    }


    /**
     * Singleton getter.
     * 
     * @return \PhpTaskDaemon\Daemon\Log
     */
    public static function get() {
        if ( ! self::$_instance ) {
            self::$_instance = new \Zend_Log();
            self::$_instance->addWriter( new \Zend_Log_Writer_Null() );
            self::$_instance->log( "Added Log_Writer: Null", \Zend_Log::DEBUG );
        }

        return self::$_instance;
    }


    /**
     * Write a message to the logger.
     * 
     * @param string    $message    The message to  log
     * @param integer   $level      The log level of the message.
     * 
     * @todo: Fix hard-coded $level as DEBUG in is not defined.
     */
    public static function log( $message, $level = null ) {
        if ( is_null( $level ) ) {
            $level = Config::get()->getOptionValue( self::CONFIG_DAEMON_LOG_LEVEL );
        }

        // @todo: Fix hard-coded $level as DEBUG in is not defined.
        if ( is_null( $level ) ) {
            $level = \Zend_Log::DEBUG;
        }

        return self::get()->log('[' . getmypid() . '] ' . $message, $level);
    }

}
