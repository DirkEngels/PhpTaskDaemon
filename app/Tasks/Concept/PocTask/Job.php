<?php

namespace Tasks\Concept\PocTask;

class Job extends \PhpTaskDaemon\Job\AbstractClass implements \PhpTaskDaemon\Job\InterfaceClass {

	protected $_inputFields = array('sleepTime');

}

