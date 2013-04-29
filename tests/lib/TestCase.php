<?php
class TestCase extends PHPUnit_Framework_TestCase
{
    protected $app;
    function __construct() {
        parent::__construct();

        // Setup autoloader
        $loader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
        $loader->useIncludePath(true);
        $loader->register();

        // Setup application
        $config    = require APP_PATH . '/config.php';
        $this->app = new App\Application($config);
        $this->app->setup();
    }
}
