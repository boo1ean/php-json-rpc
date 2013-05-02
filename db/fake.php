<?php
require_once dirname(__FILE__) . '/../vendor/autoload.php';

use App\Model\User;
use App\Model\Business;
use App\Model\Product;

function logg($msg) {
    echo $msg . PHP_EOL;
}

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

$faker = Faker\Factory::create();

$defaultPassword = 'test';
$userCount       = 5;
$businessCount   = 5;
$productCount    = 5;
$environment     = 'development';

// Users
logg("Create users");
for ($i = 0; $i < $userCount; $i++) {
    $user = User::create(array(
        'email'        => $faker->email,
        'password'     => $defaultPassword,
        'first_name'   => $faker->firstName,
        'last_name'    => $faker->lastName,
        'country'      => $faker->country,
        'phone_number' => $faker->phoneNumber,
        'city'         => $faker->city,
        'address'      => $faker->address
    ));

    logg("$user->email; $user->first_name $user->last_name");
}

// businesses
logg("\n\nCreate businesses");
$users = User::all();
$usersCount = count($users);
for ($i = 0; $i < $businessCount; $i++) {
    $user = $users[mt_rand(0, $userCount - 1)];
    $business = business::create(array(
        'user_id'      => $user->id,
        'name'         => $faker->company,
        'phone_number' => $faker->phoneNumber
    ));

    logg("$business->name; {$business->user->email}");
}

// Create products
logg("\n\nCreate products");
$businesses = business::all();
$businessesCount = count($businesses);
foreach ($businesses as $business) {
    logg("\n\nCreate products for $business->name");
    for ($i = 0; $i < $productCount; ++$i) {
        $product = Product::create(array(
            'business_id' => $business->id,
            'name'     => $faker->name
        ));

        logg("$product->name");
    }
}
