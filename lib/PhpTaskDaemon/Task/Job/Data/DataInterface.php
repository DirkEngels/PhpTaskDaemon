<?php
/**
 * @package PhpTaskDaemon
 * @subpackage Task\Job\Data
 * @copyright Copyright (C) 2011 Dirk Engels Websolutions. All rights reserved.
 * @author Dirk Engels <d.engels@dirkengels.com>
 * @license https://github.com/DirkEngels/PhpTaskDaemon/blob/master/doc/LICENSE
 */

namespace PhpTaskDaemon\Task\Job\Data;

interface DataInterface {

    public function getKeys();
    public function get();
    public function set($data);
    public function getVar($key);
    public function setVar($key, $value);
    public function validate();

}