<?php

namespace Tasks\Examples\Minimal;
use \PhpTaskDaemon\Task\Job as PTDTJ;
use \PhpTaskDaemon\Task\Queue as PTDTQ;
use \PhpTaskDaemon\Task\Executor as PTDTE;
use \PhpTaskDaemon\Task\Queue\Statistics as PTDTQS;

// Manager
class Manager extends \PhpTaskDaemon\Task\Manager\Interval {
}

// Job
class Job extends PTDTJ\BaseClass {
}

// Queue
class Queue extends PTDTQ\BaseClass {
}

// Manager
class Executor extends PTDTE\BaseClass {
}