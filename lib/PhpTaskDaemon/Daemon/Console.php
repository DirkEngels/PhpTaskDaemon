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
* The main Daemon class is responsible for starting, stopping and monitoring
* the daemon. It accepts command line arguments to set daemon daemon options.
* The start method creates an instance of Dew_Daemon_Daemon and contains the 
* methods for setup a logger, read the config, daemonize the process and set 
* the uid/gui of the process. After that the daemon instance starts all the 
* managers.
*/
class Console {

    /**
     * Configuration Object
     * @var Zend_Config
     */
    protected $_config;

    /**
     * Console options object
     * @var Zend_Console_Getopt
     */
    protected $_consoleOpts;

    /**
     * 
     * Daemon run object
     * @var Daemon
     */
    protected $_instance;


    /**
     * 
     * Daemon constructor method
     * @param \Zend_Console_Getopt $consoleOpts
     */
    public function __construct(Daemon $instance = null) {
        // Initialize command line arguments
        $this->setDaemon($instance);
        $this->setConsoleOpts();
    }


    /**
     * 
     * Returns an object containing the console arguments.
     * @return Zend_Console_Getopt
     */
    public function getConsoleOpts() {
        // Initialize default console options
        if (is_null($this->_consoleOpts)) {
            $this->_consoleOpts = new \Zend_Console_Getopt(
                array(
                    'config-file|c-s'    => 'Configuration file (defaults: /etc/{name}.conf, {cwd}/{name}.conf)',
                    'log-file|l-s'        => 'Log file (defaults /var/log/{name}.log, {cwd}/{name}.log)',
//                    'tmp-dir|td-s'      => 'Tmp directory (defaults /tmp/,',
//                    'daemonize|d'        => 'Run in Daemon mode (default) (fork to background)',
                    'action|a=s'        => 'Action (default: start) (options: start, stop, restart, status, monitor)',
                    'list-tasks|lt'     => 'List tasks',
                    'settings|s'        => 'Display tasks settings',
//                    'task|t=s'             => 'Run single task',
                    'verbose|v-i'            => 'Verbose (level: 5)',
                    'help|h'              => 'Show help message (this message)',
                )
            );
        }
        return $this->_consoleOpts;
    }


    /**
     * 
     * Sets new console arguments
     * @param Zend_Console_Getopt $consoleOpts
     * @return $this
     */
    public function setConsoleOpts(Zend_Console_Getopt $consoleOpts = null) {
        if ($consoleOpts === null) {
            $consoleOpts = $this->getConsoleOpts();
        }

        // Parse Options
        try {
            $consoleOpts->parse();
        } catch (Zend_Console_Getopt_Exception $e) {
            echo $e->getUsageMessage();
            exit;
        }
        $this->_consoleOpts = $consoleOpts;

        return $this;
    }


    /**
     * 
     * Returns the daemon daemon object
     * @return Daemon
     */
    public function getDaemon() {
        if ($this->_instance === null) {
            $this->_instance = new Instance();
        }
        return $this->_instance;
    }


    /**
     * 
     * Sets a daemon daemon object
     * @param Daemon $instance
     * @return $this
     */
    public function setDaemon($instance) {
        $this->_instance = $instance;
        return $this;
    }


    /**
     * 
     * Gets a config object
     * @return \Zend_Config
     */
    public function getConfig() {
        if ($this->_config === null) {
            $this->_initConfig(array());
        }
        return $this->_config;
    }


    /**
     * 
     * Sets a config object
     * @param \Zend_Config $config
     * @return $this
     */
    public function setConfig($config) {
        $this->_config = $config;
    }


    /**
     * Initializes the Config component by loading configuration files passed 
     * using command line arguments and the default configuration files.
     */
    protected function _initConfig() {
        // Prepare configuration files
        $configFiles = array();
        if ($this->_consoleOpts->getOption('config-file')!='') {
            $configArguments = explode(',', $this->_consoleOpts->getOption('config-file'));
            foreach ($configArguments as $configArgument) {
                if (!strstr($configArgument, '/')) {
                    $configArgument = \APPLICATION_PATH . '/' . $configArgument;
                }
                array_push($configFiles, $configArgument);
            } 
        }

        // Initiate config
        $config = \PhpTaskDaemon\Daemon\Config::get($configFiles);
        return $config;
    }


    /**
     * Initializes the logging verbose mode
     */
    protected function _initLogVerbose() {
       // Log Verbose Output
        if ($this->_consoleOpts->getOption('verbose')) {
            $writerVerbose = new \Zend_Log_Writer_Stream('php://output');

            // Determine Log Level
            $logLevel = \Zend_Log::NOTICE;
            if ($this->_consoleOpts->getOption('verbose')>0) {
                $logLevel = (int) $this->_consoleOpts->getOption('verbose');
            }
            $writerVerbose->addFilter($logLevel);

            \PhpTaskDaemon\Daemon\Logger::get()->addWriter($writerVerbose);
            $msg = 'Adding log writer: verbose (level: ' . $logLevel . ')';
            \PhpTaskDaemon\Daemon\Logger::get()->log($msg, \Zend_Log::DEBUG);
        }
    }


    /**
     * Initalizes the Logger component to save log messages to a file based on
     * the command line arguments and/or configuration files. 
     * @throws \Exception
     */
    protected function _initLogFile() {
        if ($this->_consoleOpts->getOption('log-file')) {
            $logFile =  getcwd() . '/' . $this->_consoleOpts->getOption('log-file');
        } else {
            $logFile = Config::get()->getOption('log.file');
            if (substr($logFile, 0, 1)!='/') {
                $logFile = realpath(\APPLICATION_PATH . '/..') . '/' . $logFile;
            }
        }

        // Create logfile if not exists
        if (!file_exists($logFile)) {
            try {
                touch($logFile);
            } catch (\Exception $e) {
                throw new \Exception('Cannot create log file: ' . $logFile);
            }
        }

        // Adding logfile
        $writerFile = new \Zend_Log_Writer_Stream($logFile);
        \PhpTaskDaemon\Daemon\Logger::get()->addWriter($writerFile);
        \PhpTaskDaemon\Daemon\Logger::get()->log('Adding log writer: ' . $logFile, \Zend_Log::DEBUG);
    }


    /**
     * Main function to scan all tasks by scanning directories and 
     * configuration files for executors.
     * @return array
     */
    public function scanTasks() {
        $tasks = array_merge(
            // Configuration
            $this->scanTasksInConfig(
                \PhpTaskDaemon\Daemon\Config::get()
            ),
            // Directories
            $this->scanTasksInDirs(
                APPLICATION_PATH . '/Tasks/'
            )
        );
        \PhpTaskDaemon\Daemon\Logger::get()->log('---------------------------------', \Zend_Log::DEBUG);
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
    public function scanTasksInDirs($dir, $subdir = null) {
        if (!is_dir($dir . '/' . $subdir)) {
            throw new \Exception('Directory does not exists');
        }

        $items = scandir($dir . '/' . $subdir);
        $tasks = array();
        $defaultClasses = array('Executor', 'Queue', 'Manager', 'Job');
        foreach($items as $item) {
            if ($item== '.' || $item == '..') { continue; }
            $base = (is_null($subdir)) ? $item : $subdir . '/'. $item;
            if (preg_match('/Executor.php$/', $base)) {
                // Try manager file
                if (class_exists(preg_replace('#/#', '\\', 'Tasks/' . substr($base, 0, -4)))) {
                    \PhpTaskDaemon\Daemon\Logger::get()->log(
                        "Found executor file: /Tasks/" . $base, 
                        \Zend_Log::DEBUG
                    );
                    array_push($tasks, substr($base, 0, -13));
                }
            } elseif (is_dir($dir . '/' . $base)) {
                // Load recursively
                $tasks = array_merge(
                    $tasks, 
                    $this->scanTasksInDirs($dir, $base)
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
    public function scanTasksInConfig($config) {
        return array();
    }


    /**
     * 
     * Reads the command line arguments and invokes the selected action.
     */
    public function run() {
        try {
            // Set verbose mode (--verbose)
            $this->_initLogVerbose();

            // Initialize Configuration
            $this->_initConfig();
            
            // List Tasks & exit (--list-tasks)
            $this->listTasks();

            // Display Settings & exit (--settings)
            $this->displaySettings();

            // Add Log Files
            $this->_initLogFile();

            // Check action, otherwise display help
            $action = $this->_consoleOpts->getOption('action');
            $allActions = array('start', 'stop', 'restart', 'status', 'monitor');
            if (in_array($action, $allActions))  {
                // Perform action
                $this->$action();
                exit;
            }
            $this->help();
            
        } catch (\Exception $e) {
            Logger::get()->log('FATAL EXCEPTION: ' . $e->getMessage(), \Zend_Log::CRIT);
        }
        exit;
    }


    /**
     * 
     * Lists the current loaded tasks. 
     */
    public function listTasks() {
        if ($this->_consoleOpts->getOption('list-tasks')) {
            $tasks = $this->scanTasks();
            foreach ($tasks as $task) {
                echo $task . "\n";
            }
            exit;
        }
    }


    /**
     * Displays the configuration settings for each tasks.
     */ 
    public function displaySettings() {
        if ($this->_consoleOpts->getOption('settings')) {
            $tasks = $this->scanTasks();
    
            echo "Tasks\n";
            echo "=====\n\n";
    
            echo "Examples\\Minimal\n";
            echo "-----------------\n";
            echo "\tProcess:\t\tSame\t\t\t(default)\n";
            echo "\tTrigger:\t\tInterval\t\t(default)\n";
                echo "\t- sleepTime:\t\t3\t\t\t(default)\n";
            echo "\tStatus:\t\t\tNone\t\t\t(default)\n";
            echo "\tStatistics:\t\tNone\t\t\t(default)\n";
            echo "\tLogger:\t\t\tNone\t\t\t(default)\n";
            echo "\n";
    
            echo "Examples\\Parallel\n";
            echo "-----------------\n";
            echo "\tProcess:\t\tParallel\t\t(config)\n";
                echo "\t- maxProcesses:\t\t3\t\t\t(default)\n";
            echo "\tTrigger:\t\tCron\t\t\t(default)\n";
                echo "\t- cronTime:\t\t*/15 * * * *\t\t(default)\n";
            echo "\tStatus:\t\t\tNone\t\t\t(default)\n";
            echo "\tStatistics:\t\tNone\t\t\t(default)\n";
            echo "\tLogger:\t\t\tDataBase\t\t(default)\n";
            echo "\n";

            foreach($tasks as $nr => $taskName) {
                echo "- " . $taskName . "\n";
            }
            echo "\n";
            exit;
        }
    }


    /**
     * 
     * Action: Start Daemon
     */
    public function start() {
        $tasks = $this->scanTasks();

        // Initialize daemon
        foreach($tasks as $task) {
            \PhpTaskDaemon\Daemon\Logger::get()->log('Loading task: ' . $task, \Zend_Log::INFO);
            try {
                $taskManager = \PhpTaskDaemon\Task\Factory::get($task);
                $this->getDaemon()->addManager($taskManager);
            } catch (\Exception $e) {
                throw new \Exception('Failed loading task: ' . $task);
            }
        }
        \PhpTaskDaemon\Daemon\Logger::get()->log('---------------------------------', \Zend_Log::DEBUG);

        // Start the Daemon
        $this->getDaemon()->start();
        \PhpTaskDaemon\Daemon\Logger::get()->log('---------------------------------', \Zend_Log::DEBUG);
    }


    /**
     * 
     * Action: Stop daemon 
     */
    public function stop() {
        if (!$this->getDaemon()->isRunning()) {
            echo 'Daemon is NOT running!!!' . "\n";
        } else {    
            echo 'Terminating application  !!!' . "\n";
            $this->getDaemon()->stop();
        }

        exit();
    }


    /**
     * Alias for stopping and restarting the daemon.
     */
    public function restart() {
        $this->stop();
        $this->start();
        exit;
    }


    /**
     * 
     * Action: Get daemon status
     */
    public function status() {
        
        $status = State::getState();
        if ($status['pid'] === null) {
            echo "Daemon not running\n";
            exit;
        }
        echo var_dump($status);

        echo "PhpTaskDaemon - Status\n";
        echo "==========================\n";
        echo "\n";
        if (count($status['childs']) == 0) {
            echo "No processes!\n";
        } else {
            echo "Processes (" . count($status['childs']) . ")\n";

            foreach ($status['childs'] as $childPid) {
                $managerData = $status['task-' . $childPid];
                echo " - [" . $childPid . "]: " . $status['status-' . $childPid] . "\t(Queued: " . $managerData['statistics']['queued'] . "\tDone: " . $managerData['statistics']['done'] . "\tFailed:" . $managerData['statistics']['failed'] . ")\n";
                echo "  - [" . $childPid . "]: (" . $managerData['status']['percentage'] . ") => " . $managerData['status']['message'] . "\n";
            }

        }
        return true;
    }


    /**
     * 
     * Displays the current tasks and activities of the daemon. The monitor 
     * action refreshes every x milliseconds.
     */
    public function monitor() {
        $out  = "PhpTaskDaemon - Monitoring\n" .
                "==========================\n";
        echo "Function not yet implemented\n";
    }


    /**
     * 
     * Displays a help message containing usage instructions.
     */
    public function help() {
        echo $this->_consoleOpts->getUsageMessage();
        exit;
    }

}
