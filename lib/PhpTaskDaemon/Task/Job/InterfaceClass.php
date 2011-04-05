<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Job
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Job;

interface InterfaceClass {

    public function getJobId();
    public function setJobId($jobId);
    public function getInputVar($key);
    public function setInputVar($key, $value);
    public function checkInput();

}