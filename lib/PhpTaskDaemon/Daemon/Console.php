<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Daemon
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon;

use PhpTaskDaemon\Daemon\Config;

/**
* The main Console class is responsible for starting, stopping and monitoring
* the daemon. It accepts command line arguments to set daemon daemon options.
* The start method creates an Instance object 
*/
class Console {

    /**
     * Console options object
     * @var Zend_Console_Getopt
     */
    protected $_consoleOpts;

    /**
     * 
     * Instance run object
     * @var Instance
     */
    protected $_instance;


    /**
     * 
     * Daemon constructor method
     * @param \Zend_Console_Getopt $consoleOpts
     */
    public function __construct(Instance $instance = NULL) {
        // Initialize command line arguments
        $this->setInstance($instance);
        $this->setConsoleOpts();
    }


    /**
     * 
     * Returns an object containing the console arguments.
     * @return Zend_Console_Getopt
     */
    public function getConsoleOpts() {
        // Initialize default console options
        if (is_NULL($this->_consoleOpts)) {
            $this->_consoleOpts = new \Zend_Console_Getopt(
                array(
                    'config-file|c-s'    => 'Configuration file (defaults: /etc/{name}.conf, {cwd}/{name}.conf)',
                    'log-file|l-s'        => 'Log file (defaults /var/log/{name}.log, {cwd}/{name}.log)',
                    'action|a=s'        => 'Action (default: start) (options: start, stop, restart, status, monitor)',
                    'list-tasks|lt'     => 'List tasks',
                    'settings|s'        => 'Display tasks settings',
                    'task|t=s'             => 'Run single task',
                    'verbose|v-i'            => 'Verbose',
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
    public function setConsoleOpts(Zend_Console_Getopt $consoleOpts = NULL) {
        if ($consoleOpts === NULL) {
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
     * Returns the daemon instance object
     * @return Instance
     */
    public function getInstance() {
        if ($this->_instance === NULL) {
            $this->_instance = new Instance();
        }
        return $this->_instance;
    }


    /**
     * 
     * Sets a daemon instance object
     * @param Instance $instance
     * @return $this
     */
    public function setInstance($instance) {
        $this->_instance = $instance;
        return $this;
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
            $this->settings();

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
            $taskLoader = new Tasks();
            $tasks = $taskLoader->scan();
            if (count($tasks)==0) {
                echo "No tasks found!\n";
            } else {
                foreach ($tasks as $task) {
                    echo $task . "\n";
                }
            }
            exit;
        }
    }


    /**
     * Displays the configuration settings for each tasks.
     */ 
    public function settings() {
        if ($this->_consoleOpts->getOption('settings')) {
            $this->_settingsDaemon();
//            $this->_settingsDefaults();
//            $this->_settingsTasks();
            echo "\n";
            exit;
        }
    }


    /**
     * 
     * Action: Start Daemon
     */
    public function start() {
        $tasks = new Tasks();
        $taskNames = $tasks->scan();

        // Initialize daemon tasks
        foreach($taskNames as $taskName) {
            $tasks->loadManagerByTaskName($taskName);
        }
        $this->getInstance()->setTasks($tasks);

        // Start the Daemon
        $this->getInstance()->start();
    }


    /**
     * 
     * Action: Stop daemon 
     */
    public function stop() {
        if (!$this->getInstance()->isRunning()) {
            echo 'Daemon is NOT running!!!' . "\n";
        } else {    
            echo 'Terminating application  !!!' . "\n";
            $this->getInstance()->stop();
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
        if ($status['pid'] === NULL) {
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
        return TRUE;
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
            $logLevel = \Zend_Log::ERR;
            if ($this->_consoleOpts->getOption('verbose')>1) {
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
            $logFile = Config::get()->getOptionValue('log.file');
            if (substr($logFile, 0, 1)!='/') {
                $logFile = realpath(\APPLICATION_PATH) . '/' . $logFile;
            }
        }

        // Create logfile if not exists
        if (!file_exists($logFile)) {
            try {
                touch($logFile);
                // Adding logfile
                $writerFile = new \Zend_Log_Writer_Stream($logFile);
                \PhpTaskDaemon\Daemon\Logger::get()->addWriter($writerFile);
                \PhpTaskDaemon\Daemon\Logger::get()->log('Adding log writer: ' . $logFile, \Zend_Log::DEBUG);
            } catch (\Exception $e) {
                \PhpTaskDaemon\Daemon\Logger::get()->log('Cannot create log file: ' . $logFile, \Zend_Log::ALERT);
            }
        }
    }


    protected function _settingsDaemon() {
        echo "Daemon Settings\n";
        echo "===============\n\n";

        echo "Global\n";
        echo "------\n";
        echo "- Namespace:\t\t" . Config::get()->getOptionValue('daemon.global.namespace') . "\n";
        echo "- Interrupt:\t\t" . Config::get()->getOptionValue('daemon.global.interrupt') . "\n";
        echo "- IPC:\t\t\t" . Config::get()->getOptionValue('daemon.global.ipc') . "\n";
        echo "\n";

        echo "Paths\n";
        echo "-----\n";
        echo "- App Dir:\t\t" . \APPLICATION_PATH . '/'. "\n";
        echo "- Task Dir:\t\t" . \APPLICATION_PATH . '/'. Config::get()->getOptionValue('daemon.global.taskdir') . "\n";
        echo "- Tmp Dir:\t\t" . \APPLICATION_PATH . '/'. Config::get()->getOptionValue('daemon.global.tmpdir') . "\n";
        echo "\n";

        echo "Database\n";
        echo "--------\n";
        echo "- Adapter:\t\t" . Config::get()->getOptionValue('daemon.db.adapter') . "\n";
        echo "- Database:\t\t" . Config::get()->getOptionValue('daemon.db.params.dbname') . "\n";
        echo "- Host:\t\t\t" . Config::get()->getOptionValue('daemon.db.params.host') . "\n";
        echo "- Username:\t\t" . Config::get()->getOptionValue('daemon.db.params.username') . "\n";
        echo "\n";

        echo "Log\n";
        echo "---\n";
        echo "- File:\t\t\t" . \APPLICATION_PATH . '/'. Config::get()->getOptionValue('daemon.log.file') . "\n";
        echo "- Level:\t\t" . Config::get()->getOptionValue('daemon.log.level') . "\n";
        echo "\n";

        echo "\n";
    }


    protected function _settingsDefaults() {
        echo "Tasks Default Settings\n";
        echo "======================\n\n";

        echo "Global\n";
        echo "------\n";
        echo "- Namespace:\t\t" . Config::get()->getOptionValue('tasks.defaults.namespace') . "\n";
        echo "- IPC:\t\t\t" . Config::get()->getOptionValue('tasks.defaults.namespace') . "\n";
        echo "\n";

        echo "Trigger\n";
        echo "-------\n";
        echo "- Default:\t\t" . Config::get()->getOptionValue('tasks.defaults.manager.trigger.type') . "\n";
        echo "- Types:\t\tInterval, Cron, Gearman\n";
        echo "- Interval\n";
//        echo "\t- Time:\t\t" . Config::get()->getOptionValue('tasks.defaults.manager.trigger.interval.time') . "\n";
        echo "- Cron\n";
//        echo "\t- Interval:\t" . Config::get()->getOptionValue('tasks.defaults.manager.trigger.cron.default') . "\n";
        echo "\n";

        echo "Process\n";
        echo "-------\n";
        echo "- Type:\t\t\t" . Config::get()->getOptionValue('tasks.defaults.manager.process.type') . "\n";
        echo "- Parallel\n";
        echo "\t- Childs:\t" . Config::get()->getOptionValue('tasks.defaults.manager.process.parallel.childs') . "\n";
        echo "\n";


        echo "\n";
    }


    protected function _settingsTasks() {
        echo "Tasks Specific Settings\n";
        echo "=======================\n\n";

        $tasks = array();
        try {
            $tasks = $this->scanTasks();
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        if (count($tasks)>0) {
            foreach($tasks as $nr => $taskName) {
                echo $taskName . "\n";
                echo str_repeat('-', strlen($taskName)) . "\n";
                echo "\tProcess:\t\t" . Config::get()->getOptionValue('manager.process.type') . "\t\t(" . Config::get()->getOptionValue('manager.process.type') . ")\n";
            }
        } else {
            echo "No tasks found!!!";
        }

        echo "\n\n";
    }

}
