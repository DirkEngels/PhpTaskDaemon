<?php

namespace Tasks\Concept\CopyTask;
use \PhpTaskDaemon\Task\Job as PTDTJ;

class Job extends PTDTJ\AbstractClass implements PTDTJ\InterfaceClass {

	protected $_inputFields = array('sleepTime');

}

