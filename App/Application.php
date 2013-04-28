<?php
namespace App;

class Application
{
    /**
     * Config array
     */
    protected $config;

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
        $server = new \Junior\Server(new \App\Rpc\Methods());
        $server->process();
    }

    /**
     * Setup db and stuff
     * @return void
     */
    protected function setup() {
        $connections = $this->config['db']['connections'];
        \ActiveRecord\Config::initialize(function($cfg) use ($connections) {
            $cfg->set_model_directory(APP_PATH . '/models');
            $cfg->set_connections($connections);
        });
    }
}
