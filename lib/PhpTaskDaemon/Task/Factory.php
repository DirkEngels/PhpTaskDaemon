<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task;
use \PhpTaskDaemon\Task\Exception as Exception;
use \PhpTaskDaemon\Daemon;

/**
 * The Task Factory object uses the Factory Design Pattern for instantiating
 * task related objects. It handles different kind of components and is also
 * able to create a complete task component instantiation. (Which is basically
 * a manager object with manager trigger and manager process instances, a queue
 * and queue statistics instances and also an executor and executor status
 * instances, which are injected into the manager base class.  
 *
 */
class Factory {
    const TYPE_MANAGER = 'manager';

    const TYPE_TRIGGER = 'trigger';
    const TYPE_QUEUE = 'queue';
    const TYPE_STATISTICS = 'statistics';

    const TYPE_PROCESS = 'process';
    const TYPE_EXECUTOR = 'executor';
    const TYPE_STATUS = 'status';


    /**
     * Instantiates a new Manager object and injects all needed components 
     * based on the class definitions, configurations settings and defaults.
     * @param $taskName
     * @return \PhpTaskDaemon\Task\Manager\AbstractClass
     */
    public static function get($taskName) {
        // Base Manager
        $manager = self::getManager($taskName);

        // Trigger, Queue & Statistics
        $manager->setTrigger(
            self::getComponentType($taskName, self::TYPE_TRIGGER)
        );
        $manager->getTrigger()->setQueue(
            self::getComponentType($taskName, self::TYPE_QUEUE)
        );
        $manager->getTrigger()->getQueue()->setStatistics(
            self::getComponentType($taskName, self::TYPE_STATISTICS)
        );

        // Process, Executor & Status
        $manager->setProcess(
            self::getComponentType($taskName, self::TYPE_PROCESS)
        );
        $manager->getProcess()->setExecutor(
            self::getComponentType($taskName, self::TYPE_EXECUTOR)
        );
        $manager->getProcess()->getExecutor()->setStatus(
            self::getComponentType($taskName, self::TYPE_STATUS)
        );

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
     * Returns the manager trigger for the specified task
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Manager\Trigger\AbstractClass
     */
    public static function getManager($taskName) {
        return self::getComponentType($taskName, self::TYPE_MANAGER);
    }


    /**
     * Returns the manager trigger for the specified task
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Manager\Trigger\AbstractClass
     */
    public static function getManagerTrigger($taskName) {
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
    protected function _getObjectClass($taskName, $objectType) {
        $className = self::_getClassName($taskName, $objectType);

        $msg = 'Trying ' . $objectType . ' class component: ' . self::_getClassName($taskName, $objectType);
        \PhpTaskDaemon\Daemon\Logger::get()->log($msg, \Zend_Log::DEBUG);

        if (class_exists($className)) {
            $msg = 'Found ' . $objectType . ' class component: ' . $className;
            \PhpTaskDaemon\Daemon\Logger::get()->log($msg, \Zend_Log::NOTICE);
            return new $className();
        }
        return false;
    }


    /**
     * Checks if task specific configuration options for the objectType are
     * set.
     * @param string $taskName
     * @param string $objectType
     * @return null|stdClass
     */
    protected function _getObjectConfig($taskName, $objectType) {
        $msg = 'Trying ' . $objectType . ' config component: ' . $taskName;
        \PhpTaskDaemon\Daemon\Logger::get()->log($msg, \Zend_Log::DEBUG);

        $configType = ucfirst(
            \PhpTaskDaemon\Daemon\Config::get()->getOptionValue(
                strtolower($objectType) . '.type', 
                $taskName
            )
        );
        $objectClassName = '\\PhpTaskDaemon\\Task\\Manager\\' . $configType;
        $msg = 'Testing class: ' . $objectClassName;
        \PhpTaskDaemon\Daemon\Logger::get()->log($msg, \Zend_Log::DEBUG);
        if (class_exists($objectClassName, true)) {
            $msg = 'Found ' . $objectType . ' config component: ' . $taskName;
            \PhpTaskDaemon\Daemon\Logger::get()->log($msg, \Zend_Log::NOTICE);
            $object = new $objectClassName();
            return $object;
        }
        return false;
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
            case 'trigger':
                return new \PhpTaskDaemon\Task\Manager\Trigger\Interval();
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

        return null;
    }

}