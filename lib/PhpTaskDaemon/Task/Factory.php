<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task;
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
    const TYPE_STATISTICS = 'statistics';

    const TYPE_PROCESS = 'process';
    const TYPE_EXECUTOR = 'executor';
    const TYPE_STATUS = 'status';

    const IPC_QUEUE = 'queue';
    const IPC_EXECUTOR = 'executor';

    /**
     * Instantiates a new Manager object and injects all needed components 
     * based on the class definitions, configurations settings and defaults.
     * @param $taskName
     * @return \PhpTaskDaemon\Task\Manager\AbstractClass
     */
    public static function get($taskName) {
        $msg = 'Task Factory: ' . $taskName;
        \PhpTaskDaemon\Daemon\Logger::get()->log($msg, \Zend_Log::DEBUG);
        \PhpTaskDaemon\Daemon\Logger::get()->log('----------', \Zend_Log::DEBUG);

        $executor = self::getComponentType($taskName, self::TYPE_EXECUTOR);
        if ($executor instanceof \PhpTaskDaemon\Task\Executor\DefaultClass) {
            throw new \Exception('Task has no defined executor');
        }

        // Base Manager
        $manager = self::getManager($taskName);

        // Timer
        $manager->setTimer(
            self::getComponentType($taskName, self::TYPE_TRIGGER)
        );

        // Process
        $manager->setProcess(
            self::getComponentType($taskName, self::TYPE_PROCESS)
        );

        // Queue & Statistics
        $manager->getProcess()->setQueue(
            self::getComponentType($taskName, self::TYPE_QUEUE)
        );
        $manager->getProcess()->getQueue()->setStatistics(
            self::getComponentType($taskName, self::TYPE_STATISTICS)
        );

        // Executor & Status
        $manager->getProcess()->setExecutor(
            $executor
        );
        $manager->getProcess()->getExecutor()->setStatus(
            self::getComponentType($taskName, self::TYPE_STATUS)
        );

        \PhpTaskDaemon\Daemon\Logger::get()->log('----------', \Zend_Log::DEBUG);
        return $manager;
    }


    /**
     * Returns an object of the specified objectType based on the taskName.  
     * @param string $taskName
     * @param string $objectType
     * @return stdClass
     */
    public static function getComponentType($taskName, $objectType) {
        // First: Check if the class has been overloaded
        $object = self::_getObjectClass($taskName, $objectType);

        if (!is_object($object)) {
            // Second: Check configuration
            $object = self::_getObjectConfig($taskName, $objectType);
        }

        if (!is_object($object)) {
            // Finally: Try the hard code default
            $object = self::_getObjectDefault($taskName, $objectType);
        }

        return $object;
    }


    /**
     * Returns the manager timer for the specified task
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Manager\Timer\AbstractClass
     */
    public static function getManager($taskName) {
        return self::getComponentType($taskName, self::TYPE_MANAGER);
    }


    /**
     * Returns the manager timer for the specified task
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Manager\Timer\AbstractClass
     */
    public static function getManagerTimer($taskName) {
        return self::getComponentType($taskName, self::TYPE_TRIGGER);
    }


    /**
     * Returns the manager process for the specified task
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Manager\Process\AbstractClass
     */
    public static function getManagerProcess($taskName) {
        return self::getComponentType($taskName, self::TYPE_PROCESS);
    }


    /**
     * Returns the executor status for the specified task
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Executor\Status\AbstractClass
     */
    public static function getExecutor($taskName) {
        return self::getComponentType($taskName, self::TYPE_EXECUTOR);
    }


    /**
     * Returns the executor status for the specified task
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Executor\Status\AbstractClass
     */
    public static function getExecutorStatus($taskName) {
        return self::getComponentType($taskName, self::TYPE_STATUS);
    }


    /**
     * Returns the queue for the specified task
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Queue\AbstractClass
     */
    public static function getQueue($taskName) {
        return self::getComponentType($taskName, self::TYPE_QUEUE);
    }


    /**
     * Returns the queue statistics for the specified task
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Queue\Statistics\AbstractClass
     */
    public static function getQueueStatistics($taskName) {
        return self::getComponentType($taskName, self::TYPE_STATISTICS);
    }


    public static function getIpc($taskName, $type = self::IPC_QUEUE) {
        $ipcClass = '\\PhpTaskDaemon\\Daemon\\Ipc\\' . Config::get()->getOptionValue('global.ipc');
        if (!class_exists($ipcClass)) {
            $ipcClass = '\\PhpTaskDaemon\\Daemon\\Ipc\\None';
        }
        return new $ipcClass('phptaskdaemond-' . $type . '-' . getmypid());
    }


    /**
     * Returns the classname based on the taskName and objectType
     * @param string $taskName
     * @param string $objectType
     * @return string
     */
    protected function _getClassName($taskName, $objectType) {
        return Daemon\Config::get()->getOptionValue('global.namespace') . '\\'. str_replace('/', '\\', $taskName) . '\\' . ucfirst($objectType);
    }


    /**
     * Returns the config name based on the task name.
     * @param unknown_type $objectType
     */
    protected static function _getConfigName($taskName) {
        return strtolower(str_replace('\\', '.', $taskName));
    }


    /**
     * Checks if a objectType class of a specific manager exists
     * @param string $taskName
     * @param string $objectType
     * @return null|stdClass
     */
    protected static function _getObjectClass($taskName, $objectType) {
        $className = self::_getClassName($taskName, $objectType);

        $msg = 'Trying ' . $objectType . ' class component: ' . self::_getClassName($taskName, $objectType);
        \PhpTaskDaemon\Daemon\Logger::get()->log($msg, \Zend_Log::DEBUG);

        if (class_exists($className)) {
            $msg = 'Found ' . $objectType . ' class component: ' . $className;
            \PhpTaskDaemon\Daemon\Logger::get()->log($msg, \Zend_Log::NOTICE);
            return new $className();
        }
        return NULL;
    }


    /**
     * Checks if task specific configuration options for the objectType are
     * set.
     * @param string $taskName
     * @param string $objectType
     * @return null|stdClass
     */
    protected static function _getObjectConfig($taskName, $objectType) {
        $msg = 'Trying ' . $objectType . ' config component: ' . $taskName;
        \PhpTaskDaemon\Daemon\Logger::get()->log($msg, \Zend_Log::DEBUG);

        $configType = ucfirst(
            \PhpTaskDaemon\Daemon\Config::get()->getOptionValue(
                strtolower($objectType) . '.type', 
                $taskName
            )
        );

        $nameSpace = \PhpTaskDaemon\Daemon\Config::get()->getOptionValue(
            'global.namespace'
        );

        $objectClassName = self::_getObjectConfigNamespace($objectType) . '\\' . $configType;

        $msg = 'Testing class (' . $taskName . '): ' . $objectClassName;
        \PhpTaskDaemon\Daemon\Logger::get()->log($msg, \Zend_Log::DEBUG);

        if (class_exists($objectClassName, true)) {
            $msg = 'Found ' . $objectType . ' config component: ' . $taskName;
            \PhpTaskDaemon\Daemon\Logger::get()->log($msg, \Zend_Log::NOTICE);
            $object = new $objectClassName();
            return $object;
        }

        return NULL;
    }


    protected static function _getObjectConfigNamespace($objectType) {
        $nameSpace = '\\PhpTaskDaemon\\Task\\';
        switch($objectType) {
            case 'manager':
                $nameSpace .= 'Manager';
                break;
            case 'trigger':
                $nameSpace .= 'Manager\\Trigger';
                break;
            case 'process':
                $nameSpace .= 'Manager\\Process';
                break;
            case 'queue':
                $nameSpace .= 'Manager\\Queue';
                break;
            case 'statistics':
                $nameSpace .= 'Manager\\Queue\\Statistics';
                break;
            case 'executor':
                $nameSpace .= 'Manager\\Executor';
                break;
            case 'status':
                $nameSpace .= 'Manager\\Executor\\Status';
                break;
            default:
                $nameSpace .= 'Manager';
                break;
        }
        return $nameSpace;
    }


    /**
     * Returns the hardcoded default object for a specific type.
     * @param string $objectType
     * @return null|StdClass
     */
    protected function _getObjectDefault($taskName, $objectType) {
        $msg = 'Defaulting ' . $objectType . ' component: ' . $taskName . ' => Default';
        \PhpTaskDaemon\Daemon\Logger::get()->log($msg, \Zend_Log::NOTICE);

        switch($objectType) {
            case 'manager':
                return new \PhpTaskDaemon\Task\Manager\DefaultClass(
                    self::getComponentType($taskName, self::TYPE_EXECUTOR)
                );
            case 'timer':
                return new \PhpTaskDaemon\Task\Manager\Timer\Interval();
            case 'queue':
                return new \PhpTaskDaemon\Task\Queue\DefaultClass();
            case 'statistics':
                return new \PhpTaskDaemon\Task\Queue\Statistics\DefaultClass();
            case 'process':
                return new \PhpTaskDaemon\Task\Manager\Process\Same();
            case 'executor':
                return new \PhpTaskDaemon\Task\Executor\DefaultClass();
            case 'status':
                return new \PhpTaskDaemon\Task\Executor\Status\DefaultClass();
        }
        throw new Exception\UndefinedObjectType('Unknown object type: ' . $objectType);

        return NULL;
    }

}
