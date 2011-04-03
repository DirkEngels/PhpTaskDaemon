<?php

namespace Tasks\Concept\PocTask;
use \PhpTaskDaemon\Task\Job as PTDTJ;

class Job extends PTDTJ\AbstractClass implements PTDTJ\InterfaceClass {

	protected $_inputFields = array('sleepTime');

}

