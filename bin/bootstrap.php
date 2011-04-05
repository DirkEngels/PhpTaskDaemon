<?php

// Set include paths
define('PROJECT_ROOT', realpath(__DIR__ .'/../'));
define('APPLICATION_PATH', realpath(\PROJECT_ROOT .'/app'));
define('TASKDIR_PATH', realpath(\PROJECT_ROOT .'/tasks'));
define('LIBRARY_PATH', realpath(\PROJECT_ROOT .'/lib'));
define('TMP_PATH', realpath(\PROJECT_ROOT .'/tmp'));

// Include Paths
$includePaths = array(
    get_include_path(),
    \APPLICATION_PATH,
    \LIBRARY_PATH, 
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
		} else { 
//		  echo "file does not exists: " . $classConvention . "\n";
//		  echo "file does not exists: " . $classNamespaced . "\n";
		} 
//		echo "\n";
	}
}
spl_autoload_register('autoload');