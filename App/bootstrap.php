<?php

$loader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->useIncludePath(true);
$loader->register();

// DB setup
$connections = array(
    'development' => 'mysql://car_business:car_business@localhost/car_business',
    'test'        => 'mysql://car_business:car_business@localhost/car_business',
    'production'  => 'mysql://car_business:car_business@localhost/car_business'
);

ActiveRecord\Config::initialize(function($cfg) use ($connections) {
    $cfg->set_model_directory(APP_PATH . '/models');
    $cfg->set_connections($connections);
});

$server = new Junior\Server(new \App\Rpc\Methods());
$server->process();
