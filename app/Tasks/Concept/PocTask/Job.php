<?php

namespace Tasks\Concept\PocTask;
use \PhpTaskDaemon\Task\Job as PtdJ;

class Job extends PtdJ\AbstractClass implements PtdJ\InterfaceClass {

	protected $_inputFields = array('sleepTime');

}

