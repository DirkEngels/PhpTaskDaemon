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
	 * This static method returns an array with the status of all current
	 * running tasks.
	 * @return array
	 */
	public static function getStatus() {
		$pidFile = new \PhpTaskDaemon\Daemon\Pid\File(TMP_PATH . '/phptaskdaemond.pid');
		$pid = $pidFile->read();

		$status = array('pid' => $pid);
		
		if (file_exists(TMP_PATH . '/daemon.shm')) {
			$shm = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory('daemon');
			$shmKeys = $shm->getKeys();
			$status['memKeys'] = count($shmKeys); 
			foreach($shm->getKeys() as $key => $value) {
				$status[$key] = $shm->getVar($key);
			}

			// Child info
			if (isset($status['childs'])) {
				foreach($status['childs'] as $child) {
					$status['manager-' . $child] = self::_getStatusChild($child);
				}
			}
		}

		return $status;
	}
	
	/**
	 * This statis method is mainly used by the getStatus method and returns an
	 * array with all statuses of currenly running tasks of a particular
	 * manager.
	 *
	 * @param int $childPid
	 * @return array
	 */
	protected static function _getStatusChild($childPid) {
		$status = array('childPid' => $childPid);
		
		if (file_exists(TMP_PATH . '/manager-' . $childPid . '.shm')) {
			$shm = new \PhpTaskDaemon\Daemon\Ipc\SharedMemory('manager-' . $childPid);
			$shmKeys = $shm->getKeys();
			$status['memKeys'] = count($shmKeys); 
			foreach($shm->getKeys() as $key => $value) {
				$status[$key] = $shm->getVar($key);
			}
		}

		return $status;
	}
	
	public static function getStatistics() {
		
	}
	protected static function _getStatisticsChild($childPid) {
		
	}

}