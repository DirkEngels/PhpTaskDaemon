<?php

// Set include paths
define('PROJECT_ROOT', realpath(__DIR__ .'/../'));
define('APPLICATION_PATH', realpath(\PROJECT_ROOT .'/app'));
define('TASKDIR_PATH', realpath(\PROJECT_ROOT .'/app/tasks'));
define('LIBRARY_PATH', realpath(\PROJECT_ROOT .'/lib'));
define('TMP_PATH', realpath(\PROJECT_ROOT .'/tmp'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));

// Include Paths
$includePaths = array(
    get_include_path(),
    \APPLICATION_PATH,
    \LIBRARY_PATH, 
    '/usr/share/php/libzend-framework-php/',
    '/usr/share/php/PHPUnit/',
);
set_include_path(
    implode(
        PATH_SEPARATOR,
        $includePaths
    )
);

// Custom Autoloader
function __autoloadPhpTaskDaemon($className) {
    foreach($GLOBALS['includePaths'] as $path) {
        $classNamespaced = $path .'/' . str_replace('\\', '/', $className) . '.php';
        $classConvention = $path . '/' . str_replace('_','/',$className) . '.php';
        $classParent = $path . '/' . substr($className, 0, strrpos($className, '/')) . '.php';
        if (file_exists($classNamespaced)) {
            include_once ($classNamespaced);
        } elseif (file_exists($classConvention)) {
            include_once($classConvention);
        } elseif (file_exists($classParent)) {
            include_once($classParent);
        }
    }
}
spl_autoload_register('__autoloadPhpTaskDaemon');
