<?php
namespace App;

// TODO add uses
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
     * Run JSON-RPC 2.0 application
     * @return \App\Application
     */
    public function runRPC() {
        $this->container['json-rpc-server']->process();
        return $this;
    }

    /**
     * Setup application
     * @return void
     */
    public function setup() {
        $this->setupDb();
        $this->setupContainer();
        return $this;
    }

    /**
     * Get DI container
     * @return Pimple di container
     */
    public function getContainer() {
        return $this->container;
    }

    /**
     * Setup database connection
     */
    protected function setupDb() {
        $connections = $this->config['db']['connections'];
        \ActiveRecord\Config::initialize(function($cfg) use ($connections) {
            $cfg->set_model_directory(APP_PATH . '/Model');
            $cfg->set_connections($connections);
        });
    }

    /**
     * Setup di container
     */
    protected function setupContainer() {
        $this->container = new \Pimple();

        $this->container['config'] = $this->config;

        $this->container['json-rpc-server'] = $this->container->share(function($c) {
            return new \Junior\Server(new \App\Rpc\Methods($c));
        });

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
