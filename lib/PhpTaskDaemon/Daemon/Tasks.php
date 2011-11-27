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
 * 
 * The Tasks class is responsible for scanning and loading the tasks. It is
 * used by the Console class for scanning the tasks. The Tasks instance will be
 * passed to the Instance class when starting the daemon.
 */
class Tasks {

    /**
     * Array with Task Managers
     * @var array
     */
    public $managers = array();


    /**
     * Returns an array with all the managers
     * @return array
     */
    public function getManagers() {
        return $this->managers;
    }


    /**
     * Adds a manager
     * @param \PhpTaskDaemn\Task\Manager\ManagerAbstract $manager
     * @return boolean
     */
    public function addManager($manager) {
        if (!($manager instanceof \PhpTaskDaemon\Task\Manager\ManagerAbstract)) {
            throw new \Exception('Invalid Manager instance');
        }
        return array_push($this->managers, $manager);
    }


    /**
     * Load a single manager
     * @param $taskName
     * @return boolean
     */
    public function loadManagerByTaskName($taskName) {
        Logger::log('Loading task: ' . $taskName, \Zend_Log::INFO);
        try {
            $taskManager = \PhpTaskDaemon\Task\Factory::get($taskName);
            $this->addManager($taskManager);
            Logger::log('Succesfully loaded task: ' . $taskName, \Zend_Log::INFO);

        } catch (\Exception $e) {
            Logger::log('Failed loading task: ' . $taskName . ' => ' . $e->getMessage(), \Zend_Log::INFO);
            return false;
        }
        return true;
    }


    /**
     * Main function to scan all tasks by scanning directories and 
     * configuration files for executors.
     * @return array
     */
    public function scan() {
        // Configuration
//        $tasksFoundInConfig = $this->_scanTasksInConfig(
//            \PhpTaskDaemon\Daemon\Config::get()
//        );
        $tasksFoundInConfig = array();
        Logger::log('Scanned config and found ' . count($tasksFoundInConfig) . ' tasks', \Zend_Log::DEBUG);

        // Directories
        try {
            $tasksFoundInDirs = $this->_scanTasksInDirs(
                APPLICATION_PATH . '/task/'
            );
        } catch (Exception $e) {
            $tasksFoundInDirs = array();
        }
        Logger::log('Scanned directories and found ' . count($tasksFoundInDirs) . ' tasks', \Zend_Log::DEBUG);

        // Merge Tasks
        $tasks = array_merge($tasksFoundInConfig, $tasksFoundInDirs);

        return $tasks;
    }

    /**
     * 
     * Scans a directory for task managers and returns the number of loaded
     * tasks.
     * 
     * @param string $dir
     * @return integer
     */
    protected function _scanTasksInDirs($dir, $subdir = NULL) {
//        if (!is_dir($dir . '/' . $subdir)) {
//            throw new \Exception('Directory does not exists');
//        }

        $config = Config::get();
        $items = scandir($dir . '/' . $subdir);
        $tasks = array();
        $defaultClasses = array('Executor', 'Queue', 'Manager', 'Job');
        foreach($items as $item) {
            if ($item== '.' || $item == '..') { continue; }
            $base = (is_NULL($subdir)) ? $item : $subdir . '/'. $item;
                    Logger::log(
                        "Trying file: /Task/" . $base, 
                        \Zend_Log::DEBUG
                    );
            if (preg_match('/Executor.php$/', $base)) {
                // Try manager file
                $class = preg_replace('#/#', '\\', Config::get()->getOptionValue('daemon.global.namespace') .'/' . substr($base, 0, -4));
                include_once($dir . '/' . $base);

                if (class_exists('\\' . $class)) {
                    Logger::log(
                        "Found executor file: /Task/" . $base, 
                        \Zend_Log::DEBUG
                    );
                    array_push($tasks, substr($base, 0, -13));
                }
            } elseif (is_dir($dir . '/' . $base)) {
                // Load recursively
                $tasks = array_merge(
                    $tasks, 
                    $this->_scanTasksInDirs($dir, $base)
                );
            }
        }
        return $tasks;
    }


    /**
     * Scan for tasks witin the configuration files 
     * @param $config
     * @return array
     */
    protected function _scanTasksInConfig($config) {
        return array();
    }

}
