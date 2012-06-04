<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task;
use PhpTaskDaemon\Exception\FileNotFound;

use \PhpTaskDaemon\Exception as Exception;
use \PhpTaskDaemon\Daemon;

/**
 * The Task Factory object uses the Factory Design Pattern for instantiating
 * task related objects. It handles different kind of components and is also
 * able to create a complete task component instantiation. (Which is basically
 * a manager object with manager timer and manager process instances, a queue
 * and queue statistics instances and also an executor and executor status
 * instances, which are injected into the manager base class.
 *
 */
class Factory {

    const TYPE_MANAGER = 'manager';

    const TYPE_TRIGGER = 'timer';
    const TYPE_QUEUE = 'queue';

    const TYPE_PROCESS = 'process';
    const TYPE_EXECUTOR = 'executor';

    const MSG_UNKNOWN_TYPE = 'Object type is not registered as a class constant of this class: %s.';


    /**
     * Instantiates a new Manager object and injects all needed components 
     * based on the class definitions, configurations settings and defaults.
     * 
     * @param $taskName
     * @return \PhpTaskDaemon\Task\Manager\ManagerAbstract
     * @throws FileNotFound Task has no defined executor
     */
    public static function get( $taskName ) {
        $msg = 'Task Factory: ' . $taskName;
        \PhpTaskDaemon\Daemon\Logger::get()->log( $msg, \Zend_Log::DEBUG );
        \PhpTaskDaemon\Daemon\Logger::get()->log( '----------', \Zend_Log::DEBUG );

        // Verify that the executor has been defined
        $executor = self::getComponentType( $taskName, self::TYPE_EXECUTOR );
        if ( ! $executor instanceof \PhpTaskDaemon\Task\Executor\ExecutorInterface ) {
            throw new \Exception( 'Task has no defined executor' );
        }

        // Base Manager
        $manager = self::getManager( $taskName);

        // Timer
        $manager->setTimer(
            self::getComponentType( $taskName, self::TYPE_TRIGGER )
        );

        // Process
        $manager->setProcess(
            self::getComponentType( $taskName, self::TYPE_PROCESS )
        );

        // Queue
        $manager->getProcess()->setQueue(
            self::getComponentType( $taskName, self::TYPE_QUEUE )
        );

        // Executor
        $manager->getProcess()->setExecutor( $executor );

        \PhpTaskDaemon\Daemon\Logger::get()->log( '----------', \Zend_Log::DEBUG );
        return $manager;
    }


    /**
     * Returns an object of the specified objectType based on the taskName.
     * 
     * @param string $taskName
     * @param string $objectType
     * @return stdClass
     */
    public static function getComponentType($taskName, $objectType ) {
        // First: Check if the class has been overloaded
        $object = self::_getObjectClass( $taskName, $objectType );

        // Second: Check configuration
        if ( ! is_object( $object )) {
            $object = self::_getObjectConfig( $taskName, $objectType );
        }

        // Finally: Try the hard code default
        if ( ! is_object( $object )) {
            $object = self::_getObjectDefault( $taskName, $objectType );
        }

        // Panic: This is bad, throw an error!
        if ( ! is_object( $object )) {
            throw new InvalidArgumentException( 'Unknow' );
        }

        return $object;
    }


    /**
     * Returns the manager timer for the specified task.
     * 
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Manager\ManagerAbstract
     */
    public static function getManager( $taskName ) {
        $manager = self::getComponentType( $taskName, self::TYPE_MANAGER );
        $manager->setName( $taskName );
        return $manager;
    }


    /**
     * Returns the manager timer for the specified task.
     * 
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Manager\Timer\TimerAbstract
     */
    public static function getManagerTimer( $taskName ) {
        return self::getComponentType( $taskName, self::TYPE_TRIGGER );
    }


    /**
     * Returns the manager process for the specified task.
     * 
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Manager\Process\ProcessAbstract
     */
    public static function getManagerProcess( $taskName ) {
        $process = self::getComponentType( $taskName, self::TYPE_PROCESS );
        $process->setName( $taskName );
        return $process;
    }


    /**
     * Returns the executor status for the specified task.
     * 
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Executor\Status\StatusAbstract
     */
    public static function getExecutor( $taskName ) {
        return self::getComponentType( $taskName, self::TYPE_EXECUTOR );
    }

    /**
     * Returns the queue for the specified task.
     * 
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Queue\QueueAbstract
     */
    public static function getQueue( $taskName ) {
        return self::getComponentType( $taskName, self::TYPE_QUEUE );
    }


    /**
     * Returns the classname based on the taskName and objectType.

     * @param string $taskName
     * @param string $objectType
     * @return string
     */
    public static function _getClassName( $taskName, $objectType ) {
        return implode( '\\', array(
            Daemon\Config::get()->getOptionValue( 'global.namespace' ),
            str_replace( '/', '\\', $taskName ),
            ucfirst( $objectType )
        ) );
    }


    /**
     * Returns the config name based on the task name.
     * 
     * @param unknown_type $objectType
     */
    protected static function _getConfigName( $taskName ) {
        return strtolower( str_replace( '\\', '.', $taskName ) );
    }


    /**
     * Checks if a objectType class of a specific manager exists.
     * 
     * @param string $taskName
     * @param string $objectType
     * @return null|stdClass
     */
    protected static function _getObjectClass( $taskName, $objectType ) {
        $className = self::_getClassName( $taskName, $objectType );

        $msg = 'Trying ' . $objectType . ' class component: ' . self::_getClassName( $taskName, $objectType );
        \PhpTaskDaemon\Daemon\Logger::get()->log($msg, \Zend_Log::DEBUG);

        if ( class_exists( $className ) ) {
            $msg = 'Found ' . $objectType . ' class component: ' . $className;
            \PhpTaskDaemon\Daemon\Logger::get()->log( $msg, \Zend_Log::NOTICE );
            return new $className();
        }
        return NULL;
    }


    /**
     * Checks if task specific configuration options for the objectType are
     * set.
     * 
     * @param string $taskName
     * @param string $objectType
     * @return null|stdClass
     */
    protected static function _getObjectConfig( $taskName, $objectType ) {
        $msg = 'Trying ' . $objectType . ' config component: ' . $taskName;
        \PhpTaskDaemon\Daemon\Logger::get()->log( $msg, \Zend_Log::DEBUG );

        $configType = ucfirst(
            \PhpTaskDaemon\Daemon\Config::get()->getOptionValue(
                strtolower( $objectType ) . '.type',
                $taskName
            )
        );

        $nameSpace = \PhpTaskDaemon\Daemon\Config::get()->getOptionValue(
            'global.namespace'
        );

        $objectClassName = self::_getObjectConfigNamespace( $objectType ) . '\\' . $configType;

        $msg = 'Testing class (' . $taskName . '): ' . $objectClassName;
        \PhpTaskDaemon\Daemon\Logger::get()->log( $msg, \Zend_Log::DEBUG );

        if ( class_exists( $objectClassName, TRUE ) ) {
            $msg = 'Found ' . $objectType . ' config component: ' . $taskName;
            \PhpTaskDaemon\Daemon\Logger::get()->log( $msg, \Zend_Log::NOTICE );
            $object = new $objectClassName();
            return $object;
        }

        return NULL;
    }


    /**
     * Return a instance of a object type (class constant) from the
     * configuration file.
     * 
     * @param string $objectType A defined object type constant.
     * @return string Namespace of the object type.
     * @throws InvalidArgumentException MSG_UNKNOWN_TYPE
     */
    protected static function _getObjectConfigNamespace( $objectType ) {
        $nameSpace = '\\PhpTaskDaemon\\Task\\';
        switch( $objectType ) {
            case 'manager':
                $nameSpace .= 'Manager';
                break;
            case 'timer':
                $nameSpace .= 'Manager\\Timer';
                break;
            case 'process':
                $nameSpace .= 'Manager\\Process';
                break;
            case 'timer':
                $nameSpace .= 'Manager\\Timer';
                break;
            case 'queue':
                $nameSpace .= 'Queue';
                break;
            case 'executor':
                $nameSpace .= 'Executor';
                break;
            default:
                $msg = sprintf( self::MSG_UNKNOWN_TYPE, $objectType );
                throw new \InvalidArgumentException( $msg );
                break;
        }
        return $nameSpace;
    }


    /**
     * Returns the hardcoded default object for a specific type. The hard coded
     * default will only be selected when there is no task specific and no
     * global configuration option has been set.
     * 
     * @param string $objectType
     * @return null|StdClass
     * @throws InvalidArgumentException Unknown object type: {$objectType}
     */
    public static function _getObjectDefault( $taskName, $objectType ) {
        $msg = 'Defaulting ' . $objectType . ' component: ' . $taskName . ' => Default';
        \PhpTaskDaemon\Daemon\Logger::log( $msg, \Zend_Log::DEBUG );

        switch( $objectType ) {
            // Manager
            case 'manager':
                return new \PhpTaskDaemon\Task\Manager\ManagerDefault(
                    self::getComponentType( $taskName, self::TYPE_EXECUTOR )
                );
            case 'timer':
                return new \PhpTaskDaemon\Task\Manager\Timer\Interval();
            case 'queue':
                return new \PhpTaskDaemon\Task\Queue\QueueDefault();
            case 'process':
                return new \PhpTaskDaemon\Task\Manager\Process\Same();

            // Queue
            case 'queue':
                return new \PhpTaskDaemon\Task\Queue\QueueDefault();

            // Executor
            case 'executor':
                return new \PhpTaskDaemon\Task\Executor\ExecutorDefault();
        }

        throw new Exception\UndefinedObjectType( 'Unknown object type: ' . $objectType );

        return NULL;
    }

}
