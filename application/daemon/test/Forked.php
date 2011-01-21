<?php
/**
 * @author DirkEngels <d.engels@dirkengels.com>
 * @package Dew
 * @subpackage Dew_Daemon
 */

/**
 * 
 * This example class shows a task running with an forked manager. The manager
 * loads the queue and starts forking until the max setting. Before forking a 
 * task is set, which will be the childs task to execute. The manager waits 
 * till one of the child is done processing and restarts forking until the 
 * queue is empty. If the queue is empty the manager waits for a default time
 * to wait before reloading a queue. 
 *
 */

require_once(__DIR__ . '/Example.php');

class Dew_Daemon_Task_Forked extends Dew_Daemon_Task_Example {

	static protected $_managerType = Dew_Daemon_Manager_Abstract::PROCESS_TYPE_FORKED;
	
}