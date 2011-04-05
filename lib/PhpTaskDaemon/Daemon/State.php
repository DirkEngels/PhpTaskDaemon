<?php
/**
 * @package PhpTaskDaemon
 * @copyright Copyright (C) 2010 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Daemon;

class State {
	
	/**
	 * 
	 * This static method returns an array with the state (statistics + 
	 * statuses of active tasks) of all current running tasks.
	 * @return array
	 */
	public static function getState() {
		$pidFile = new \PhpTaskDaemon\Daemon\Pid\File(TMP_PATH . '/phptaskdaemond.pid');
		$pid = $pidFile->read();

		$state = array('pid' => $pid);
		
		if (file_exists(TMP_PATH . '/phptaskdaemond.shm')) {
			$shm = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory('phptaskdaemond');
			$shmKeys = $shm->getKeys();
			$state['memKeys'] = count($shmKeys); 
			foreach($shm->getKeys() as $key => $value) {
				$state[$key] = $shm->getVar($key);
			}

			// Child info
			if (isset($state['childs'])) {
				foreach($state['childs'] as $child) {
					$state['task-' . $child]['status'] = self::_getStatusChild($child);
					$state['task-' . $child]['statistics'] = self::_getStaticsChild($child);
				}
			}
		}
		return $state;
	}
	
	/**
	 * This statis method is mainly used by the getState method and returns an
	 * array with all statuses of currently running tasks of a particular
	 * manager.
	 *
	 * @param int $childPid
	 * @return array
	 */
	protected static function _getStatusChild($childPid) {
		$state = array('childPid' => $childPid);
		
		if (file_exists(TMP_PATH . '/status-' . $childPid . '.shm')) {
			$shm = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory('status-' . $childPid);
			$shmKeys = $shm->getKeys();
			foreach($shm->getKeys() as $key => $value) {
				$state[$key] = $shm->getVar($key);
			}
		}

		return $state;
	}

	/**
	 * This statis method is mainly used by the getState method and returns an
	 * array with statistics of all currently running tasks of a particular
	 * manager.
	 *
	 * @param int $childPid
	 * @return array
	 */
	protected static function _getStaticsChild($childPid) {
		$state = array('childPid' => $childPid);
		
		if (file_exists(TMP_PATH . '/statistics-' . $childPid . '.shm')) {
			$shm = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory('statistics-' . $childPid);
			$shmKeys = $shm->getKeys();
			foreach($shm->getKeys() as $key => $value) {
				$state[$key] = $shm->getVar($key);
			}
		}

		return $state;
	}
	
}