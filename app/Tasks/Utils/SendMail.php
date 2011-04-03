<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task;
use \PhpTaskDaemon\Manager as Manager;

/**
 * 
 *
 */
class SendMail extends AbstractClass implements InterfaceClass {
	/** Manager Type */
	static protected $_managerType = Manager\AbstractClass::PROCESS_TYPE_GEARMAN;
	
	/**
	 * Gearman Manager Specific Options
	 */
	protected $_gearmanAsync = true;
	protected $_gearmanForks = 4;
	
	public function loadTasks () {
		// Read mailqueue from database

	}

	public function executeTask() {
		// Try delivering the mail to @author dirk

			// Save log & delete message
			
		
			// Save log & update timestamp for retrying next time
			
	}
}
