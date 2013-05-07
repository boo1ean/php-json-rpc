<?php

use App\Model\User;
use App\Model\Business;
use App\Model\Product;
use App\Ext\Fixtures;

class TestCase extends PHPUnit_Framework_TestCase
{
    const DEFAULT_PASSWORD = 'test';

    protected $app;
    protected $container;
    protected $faker;

    public function __construct() {
        parent::__construct();

        // Setup autoloader
        $loader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
        $loader->useIncludePath(true);
        $loader->register();

        // Setup application
        $config    = require APP_PATH . '/config.php';
        $this->app = new App\Application($config);
        $this->app->setup();

        $this->container = $this->app->getContainer();
        $this->faker     = Faker\Factory::create();
        $this->fixtures  = new Fixtures();
    }

    public function setUp() {
        parent::setUp();
        $this->clearDb();
    }

    /**
     * Aggragate fixtures methods to TestCase
     */
    public function __call($method, $arguments) {
        if (!strncmp($method, $this->fixtures->methodPrefix, strlen($this->fixtures->methodPrefix))) {
            $method = new ReflectionMethod('App\\Ext\\Fixtures', $method);
            return $method->invokeArgs($this->fixtures, $arguments);
        }
    }

    public function clearDb() {
        $connection = User::connection();

        $tables = array(
            'reviews',
            'product_bookings',
            'product_orders',
            'bookings',
            'products',
            'businesses',
            'users'
        );

        $query  = "DELETE FROM";
        foreach ($tables as $table) {
            $connection->query("$query $table");
        }
    }
}
