<?php
// @codeCoverageIgnoreStart
return array(
    'db' => array(
        'connections' => array(
            'development' => 'mysql://car_business:car_business@localhost/car_business',
            'test'        => 'mysql://car_business:car_business@localhost/car_business',
            'production'  => 'mysql://car_business:car_business@localhost/car_business'
        )
    ),

    'date_format' => DateTime::W3C,

    'apple_push_notifications' => array(
        'dev'                    => false,
        'simulate'               => false,
        'certificate'            => '/path/to/your/certificate.pem',
        'certificate_passphrase' => 'myPassPhrase',
        'devices'                => array(42)
    ),

    'android_push_notifications' => array(
        'applicationID' => '123456789012',
        'apiKey'        => 'y0ur4p1k3y',
        'devices'       => array(42)
    )
);
