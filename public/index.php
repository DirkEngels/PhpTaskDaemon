<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Set include paths
define('PROJECT_ROOT', realpath(__DIR__ .'/../'));
//define('APPLICATION_PATH', realpath(PROJECT_ROOT .'/application'));
define('LIBRARY_PATH', realpath(PROJECT_ROOT .'/library'));
define('TMP_PATH', realpath(PROJECT_ROOT .'/../tmp'));

// Include Paths
$includePaths = array(
    get_include_path(),
    LIBRARY_PATH, 
    '/usr/share/php/libzend-framework-php/'
);
set_include_path(
    implode(
        PATH_SEPARATOR,
        $includePaths
    )
);

// Custom Autoloader
function autoload($className) {
	foreach($GLOBALS['includePaths'] as $path) {
		$classNamespaced = $path .'/' . str_replace('\\', '/', $className) . '.php';
		$classConvention = $path . '/' . str_replace('_','/',$className) . '.php';
		if (file_exists($classNamespaced)) {
			include_once ($classNamespaced);
		} elseif (file_exists($classConvention)) {
			include_once($classConvention);
		}
	}
}
spl_autoload_register('autoload');

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();