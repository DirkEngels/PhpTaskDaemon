<?php

include('bootstrap.php');

$taskFactory = \PhpTaskDaemon\Task\Factory::get('Minimal\\Example');
$taskManager->execute();