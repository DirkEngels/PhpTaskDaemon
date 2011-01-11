<?php
/**
 * @author DirkEngels <d.engels@dirkengels.com>
 * @package Dew
 * @subpackage Dew_Daemon_Example
 */

/**
 * 
 * The example class shows a task running as a gearman worker. The manager 
 * forks one or multiple times and starts a gearman worker. Currently only a 
 * single worker is started. When implementing a gearman task, the loadQueue 
 * method is useless because the queue is handled by the gearman job server.
 * Also the task is not executed until the queue contains one or more jobs. 
 * A gearman tasks can be triggered through the website, external commands or
 * the PhpTaskDaemon itself, using another task for triggering gearman tasks.  
 *
 */

require_once(__DIR__ . '/Example.php');

class Dew_Daemon_Task_Gearman extends Dew_Daemon_Task_Example {

	static protected $_managerType = Dew_Daemon_Manager_Abstract::PROCESS_TYPE_GEARMAN;
	
}