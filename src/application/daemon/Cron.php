<?php
/**
 * @author DirkEngels <d.engels@dirkengels.com>
 * @package Dew
 * @subpackage Dew_Daemon
 */

/**
 * 
 * The example class shows a task running as a regular cron job using the cron
 * manager. A queue is loaded and all tasks are processed sequentially. The
 * cron-like definition determines when to load a new task queue. The script
 * does not run, when it is supposed to according the cron, but has not yet
 * finished processing earlier tasks.
 *
 */

require_once(__DIR__ . '/Example.php');

class Dew_Daemon_Task_Cron extends Dew_Daemon_Task_Example {

	static protected $_managerType = Dew_Daemon_Manager_Abstract::PROCESS_TYPE_CRON;
	
}