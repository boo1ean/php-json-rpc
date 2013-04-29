<?php
defined('BASE_PATH') || define('BASE_PATH', realpath(dirname(__FILE__) . '/..'));
defined('APP_PATH')  || define('APP_PATH',  realpath(dirname(__FILE__) . '/../App'));

set_include_path(implode(PATH_SEPARATOR, array(
    BASE_PATH,
    get_include_path()
)));

require_once BASE_PATH . '/vendor/autoload.php';

// Setup autoloader
$loader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->useIncludePath(true);
$loader->register();

// Setup and run application
$config = require APP_PATH . '/config.php';
$app    = new App\Application($config);

$app->setup()
    ->runRPC();
