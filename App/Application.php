<?php
namespace App;

class Application
{
    public function run() {
        $this->configure();
        $server = new \Junior\Server(new \App\Rpc\Methods());
        $server->process();
    }

    protected function configure() {
        $connections = array(
            'development' => 'mysql://car_business:car_business@localhost/car_business',
            'test'        => 'mysql://car_business:car_business@localhost/car_business',
            'production'  => 'mysql://car_business:car_business@localhost/car_business'
        );

        \ActiveRecord\Config::initialize(function($cfg) use ($connections) {
            $cfg->set_model_directory(APP_PATH . '/models');
            $cfg->set_connections($connections);
        });
    }
}
