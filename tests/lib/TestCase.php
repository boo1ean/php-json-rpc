<?php

use App\Model\User;
use App\Model\Business;
use App\Model\Product;

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
    }

    public function setup() {
        $this->clearDb();
    }

    /**
     * Creates user
     * @return App\Model\Business
     */
    public function createUser() {
        return User::create(array(
            'email'        => $this->faker->email,
            'password'     => self::DEFAULT_PASSWORD,
            'first_name'   => $this->faker->firstName,
            'last_name'    => $this->faker->lastName,
            'country'      => $this->faker->country,
            'phone_number' => $this->faker->phoneNumber,
            'city'         => $this->faker->city,
            'address'      => $this->faker->address
        ));
    }

    /**
     * Create business
     *
     * @param integer $user_id business owner id 
     * @return App\Model\Business
     */
    public function createBusiness($user_id, $attributes = array()) {
        $attributes = array_merge(array(
            'user_id'      => $user_id,
            'name'         => $this->faker->company,
            'phone_number' => $this->faker->phoneNumber
        ), $attributes);

        return Business::create($attributes);
    }

    /**
     * Create bunch of businesses for single user
     *
     * @param integer $user_id business owner id 
     * @param integer $number number of businesses to create
     * @return array of App\Model\Business
     */
    public function createBusinesses($user_id, $count) {
        $businesses = array();
        for ($i = 0; $i < $count; ++$i) {
            $businesses[] = $this->createBusiness($user_id);
        }

        return $businesses;
    }

    /**
     * Create product for specific business
     * 
     * @param integer $business_id
     * @param array $attributes product attrs
     * @return App\Model\Product
     */
    public function createProduct($business_id, $attributes = array()) {
        $attributes = array_merge(array(
            'business_id' => $business_id,
            'name'        => $this->faker->name
        ), $attributes);

        return Product::create($attributes);
    }

    /**
     * Create bunch of products
     *
     * @param integer $business_id
     * @param integer $count number of products
     */
    public function createProducts($business_id, $count) {
        $products = array();
        for ($i = 0; $i < $count; ++$i) {
            $products[] = $this->createProduct($business_id);
        }

        return $products;
    }

    public function clearDb() {
        $connection = User::connection();

        $tables = array(
            'product_bookings',
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
