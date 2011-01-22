<?php
/**
 * @author DirkEngels <d.engels@dirkengels.com>
 * @package Dew
 * @subpackage Dew_Daemon
 */

/**
 * 
 * This example class shows a task running with an interval manager. Every task
 * can adjust the time to wait after finishing all tasks and running again. 
 * This example is called sloppy, because the time between loading a queue and
 * executing its tasks is at a random interval. 
 *
 */

require_once(__DIR__ . '/Example.php');

class Dew_Daemon_Task_Interval extends Dew_Daemon_Task_Example {

	static protected $_managerType = Dew_Daemon_Manager_Abstract::PROCESS_TYPE_INTERVAL;
	
}