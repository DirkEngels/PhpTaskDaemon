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


	public static function get($taskName) {
		return self::get2($taskName);
	}


	public static function get1($taskName) {
		// Instantiate Executor
		$executor = self::getComponentType($taskName, self::TYPE_EXECUTOR);
		$executor->setStatus(
            self::getComponentType($taskName, self::TYPE_STATUS)
		);
		
		// Instantiate Queue
		$queue = self::getComponentType($taskName, self::TYPE_QUEUE);
		$queue->setStatistics(
            self::getComponentType($taskName, self::TYPE_STATISTICS)
		);

		// Instantiate Manager
        $managerProcess = self::getComponentType($taskName, self::TYPE_PROCESS);
        $managerProcess->setExecutor($executor);
                
		$managerTrigger = self::getComponentType($taskName, self::TYPE_TRIGGER);
        $managerTrigger->setQueue($queue);
        
        return $manager;
	}


	public function get2($taskName) {
		// Base Manager
		$manager = self::getComponentType($taskName, self::TYPE_MANAGER);
		
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
		$class = $this->_getObjectClass($taskName, $objectType);
		if (is_object($class)) {
			return $class;
		}
		
		// Second: Check if the task has a specific configuration part
		$config = $this->_getObjectTaskConfig($taskName, $objectType);
		if (is_object($config)) {
			return $config;
		}
		
		// Third: Check the default config
		$default = $this->_getObjectDefaultConfig($taskName, $objectType);
		if (is_object($default)) {
			return $default;
		}
		
		// Finally: Get the hard code default
		$hardcoded = $this->_getObjectHardCoded($objectType);
		return $hardcoded;
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
        return '\\Tasks\\' . $taskName . '\\' . ucfirst($objectType);
    }


    /**
     * Checks if a objectType class of a specific manager exists
     * @param string $taskName
     * @param string $objectType
     * @return null|stdClass
     */
    protected function _getObjectClass($taskName, $objectType) {
        $className = $this->_getClassName($taskName, $objectType);
        if (class_exists($className)) {
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
    protected function _getObjectTaskConfig($taskName, $objectType) {
        $objectType = \PhpTaskDaemon\Daemon\Config::getTaskOption(
            strtolower($objectType . '.type'), 
            $taskName
        );
        $objectClassName = '\\Tasks\\' . $taskName . '\\' . $objectType;
        if (class_exists($objectClassName, true)) {
        	$object = new $objectClassName();
        	return $object;
        }
        return false;
    }


    /**
     * Checks if default configuration options for the objectType are set.
     * @param string $taskName
     * @param string $objectType
     * @return null|stdClass
     */
    protected function _getObjectDefaultConfig($taskName, $objectType) {
        $objectType = \PhpTaskDaemon\Daemon\Config::getDaemonOption(
            strtolower($objectType . '.type')
        );
        $objectClassName = '\\Tasks\\' . $taskName . '\\' . $objectType;
        if (class_exists($objectClassName, true)) {
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
    protected function _getObjectHardCoded($objectType) {
        switch($objectType) {
            case 'manager':
                return new \PhpTaskDaemon\Task\Manager\BaseClass();
            case 'trigger':
                return new \PhpTaskDaemon\Task\Manager\Trigger\BaseClass();
            case 'queue':
                return new \PhpTaskDaemon\Task\Queue\BaseClass();
            case 'statistics':
                return new \PhpTaskDaemon\Task\Queue\Statistics\BaseClass();
            case 'process':
                return new \PhpTaskDaemon\Task\Manager\Process\BaseClass();
            case 'executor':
                return new \PhpTaskDaemon\Task\Executor\BaseClass();
            case 'status':
                return new \PhpTaskDaemon\Task\Executor\Status\BaseClass();
        }
        throw new Exception\UndefinedObjectType('Unknown object type: ' . $objectType);
        
        return null;
    }

}