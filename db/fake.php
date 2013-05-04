<?php
require_once dirname(__FILE__) . '/../vendor/autoload.php';

defined('BASE_PATH') || define('BASE_PATH', realpath(dirname(__FILE__) . '/..'));
defined('APP_PATH')  || define('APP_PATH',  realpath(dirname(__FILE__) . '/../App'));

set_include_path(implode(PATH_SEPARATOR, array(
    BASE_PATH,
    get_include_path()
)));

// Setup autoloader
$loader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->useIncludePath(true);
$loader->register();

// Setup and run application
$config = require APP_PATH . '/config.php';
$app    = new App\Application($config);

$app->setup();

$generator = new App\Ext\Fixtures;
$generator->createDefaultDataSet();
