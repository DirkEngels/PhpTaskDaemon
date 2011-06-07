<?php

namespace PhpTaskDaemon\Task;

use \PhpTaskDaemon\Task\Exception as Exception;

class Factory {
	const TYPE_PROCESS = 'process';
	const TYPE_TRIGGER = 'trigger';
    const TYPE_STATUS = 'status';
	const TYPE_QUEUE = 'queue';
	const TYPE_STATISTICS = 'statistics';
	
	
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
	   return false;
	}
	
	/**
	 * Checks if default configuration options for the objectType are set.
     * @param string $taskName
     * @param string $objectType
     * @return null|stdClass
     */
	protected function _getObjectDefaultConfig($taskName, $objectType) {
	}
	
	/**
	 * Returns the hardcoded default object for a specific type.
	 * @param string $objectType
	 * @return null|StdClass
	 */
	protected function _getObjectHardCoded($objectType) {
		switch($objectType) {
			case 'process':
				return new \PhpTaskDaemon\Task\Manager\Process\BaseClass();
            case 'trigger':
                return new \PhpTaskDaemon\Task\Manager\Trigger\BaseClass();
            case 'status':
                return new \PhpTaskDaemon\Task\Executor\Status\BaseClass();
            case 'queue':
                return new \PhpTaskDaemon\Task\Queue\BaseClass();
            case 'statistics':
                return new \PhpTaskDaemon\Task\Queue\Statistics\BaseClass();
		}
		throw new Exception\UndefinedObjectType('Unknown object type: ' . $objectType);
		
		return false;
	}
	
	/**
	 * Returns an object of the specified objectType based on the taskName.  
	 * @param string $taskName
	 * @param string $objectType
	 * @return stdClass
	 */
	public static function getObjectType($taskName, $objectType) {
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
	public static function getManagerTrigger($taskName) {
		return self::getObjectType($taskName, self::TYPE_MANAGER);
	}
	
    /**
     * Returns the manager process for the specified task
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Manager\Process\AbstractClass
     */
	public static function getManagerProcess($taskName) {
		return self::getObjectType($taskName, self::TYPE_PROCESS);
	}
	
    /**
     * Returns the executor status for the specified task
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Executor\Status\AbstractClass
     */
	public static function getExecutorStatus($taskName) {
		return self::getObjectType($taskName, self::TYPE_STATUS);
	}
	
    /**
     * Returns the queue for the specified task
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Queue\AbstractClass
     */
	public static function getQueue($taskName) {
		return self::getObjectType($taskName, self::TYPE_QUEUE);
	}
	
    /**
     * Returns the queue statistics for the specified task
     * @param string $taskName
     * @return \PhpTaskDaemon\Task\Queue\Statistics\AbstractClass
     */
	public static function getQueueStatistics($taskName) {
        return self::getObjectType($taskName, self::TYPE_STATISTICS);
    }
    
}