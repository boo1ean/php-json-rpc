<?php
namespace App;

class Application
{
    /**
     * Config array
     */
    protected $config;

    /**
     * DI container
     */
    protected $container;

    /**
     * @param array config application config array
     * @return void
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Run application
     * @return void
     */
    public function run() {
        $this->setup();
        $server = new \Junior\Server(new \App\Rpc\Methods($this->container));
        $server->process();
    }

    /**
     * Setup application
     * @return void
     */
    protected function setup() {
        $this->setupDb();
        $this->setupContainer();
    }

    /**
     * Setup database connection
     */
    protected function setupDb() {
        $connections = $this->config['db']['connections'];
        \ActiveRecord\Config::initialize(function($cfg) use ($connections) {
            $cfg->set_model_directory(APP_PATH . '/models');
            $cfg->set_connections($connections);
        });
    }

    /**
     * Setup di container
     */
    protected function setupContainer() {
        $this->container = new \Pimple();

        $this->container['auth-storage'] = function($c) {
            return new \Zend\Authentication\Storage\Session();
        };

        $this->container['auth-adapter'] = function($c) {
            return new \App\Ext\Zend\Auth\Adapter();
        };

        $this->container['auth-service'] = $this->container->share(function($c) {
            return new \Zend\Authentication\AuthenticationService($c['auth-storage'], $c['auth-adapter']);
        });

        $this->container['user'] = $this->container->share(function($c) {
            return $c['auth-service']->getIdentity();
        });

        $this->container['user-service'] = $this->container->share(function($c) {
            return new \App\Service\User($c);
        });
    }
}
