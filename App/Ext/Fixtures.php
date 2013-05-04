<?php
namespace App\Ext;

use App\Model\User;
use App\Model\Business;
use App\Model\Product;
use App\Model\Booking;

class Fixtures
{
    const DEFAULT_PASSWORD   = 'test';
    const DEFAULT_BATCH_SIZE = 10;

    public $methodPrefix = 'create';

    protected $faker;
    protected $silent;

    public function __construct($silent = true) {
        $this->faker  = \Faker\Factory::create();
        $this->silent = $silent;
    }

    /**
     * Logging method
     *
     * @param string $msg log message
     * @return void
     */
    protected function log($msg) {
        if (!$this->silent) {
            $time = new \DateTime('NOW');
            $time = $time->format('c');
            echo "[{$time}] $msg\n";
        }
    }

    /**
     * Creates user
     * @return App\Model\Business
     */
    public function createUser() {
        $p = array(
            'email'        => $this->faker->email,
            'password'     => self::DEFAULT_PASSWORD,
            'first_name'   => $this->faker->firstName,
            'last_name'    => $this->faker->lastName,
            'country'      => $this->faker->country,
            'phone_number' => $this->faker->phoneNumber,
            'city'         => $this->faker->city,
            'address'      => $this->faker->address
        );

        $this->log("User created: {$p['first_name']} {$p['last_name']} {$p['email']}");
        return User::create($p);
    }

    public function createUsers($count = self::DEFAULT_BATCH_SIZE) {
        $users = array();
        for ($i = 0; $i < $count; ++$i) {
            $users[] = $this->createUser();
        }

        return $users;
    }

    /**
     * Create business
     *
     * @param integer $user_id business owner id 
     * @return App\Model\Business
     */
    public function createBusiness($user_id, $p = array()) {
        $p = array_merge(array(
            'user_id'      => $user_id,
            'name'         => $this->faker->company,
            'phone_number' => $this->faker->phoneNumber
        ), $p);

        $this->log("Business created: user_id={$p['user_id']}, {$p['name']}");
        return Business::create($p);
    }

    /**
     * Create bunch of businesses for single user
     *
     * @param integer $user_id business owner id 
     * @param integer $number number of businesses to create
     * @return array of App\Model\Business
     */
    public function createBusinesses($user_id, $count = self::DEFAULT_BATCH_SIZE) {
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
     * @param array $p product attrs
     * @return App\Model\Product
     */
    public function createProduct($business_id, $p = array()) {
        $p = array_merge(array(
            'business_id' => $business_id,
            'name'        => $this->faker->name,
            'price'        => $this->faker->randomFloat
        ), $p);

        $this->log("Product created: business_id={$p['business_id']}, {$p['name']}");
        return Product::create($p);
    }

    /**
     * Create booking for product
     *
     * @param integer $product_id
     * @param array $p bookings attrs
     * @return App\Model\Booking
     */
    public function createBooking($product_id, $p = array()) {
        $rate     = 0.2;
        $duration = $this->faker->randomNumber(60, 60 * 24);
        $price    = $duration * $rate;
        $p = array_merge(array(
            'product_id' => $product_id,
            'duration'   => $duration,
            'price'      => $price
        ), $p);

        $this->log("Booking created: product_id={$p['product_id']} {$p['duration']} minutes for \${$p['price']}");
        return Booking::create($p);
    }

    /**
     * Create bunch of bookings for specified product
     *
     * @param integer $product_id
     * @param integer $count number of bookings
     */
    public function createBookings($product_id, $count = self::DEFAULT_BATCH_SIZE) {
        $bookings = array();
        for ($i = 0; $i < $count; ++$i) {
            $bookings[] = $this->createBooking($product_id);
        }

        return $bookings;
    }

    /**
     * Create bunch of products for specified business
     *
     * @param integer $business_id
     * @param integer $count number of products
     */
    public function createProducts($business_id, $count = self::DEFAULT_BATCH_SIZE) {
        $products = array();
        for ($i = 0; $i < $count; ++$i) {
            $products[] = $this->createProduct($business_id);
        }

        return $products;
    }

    /**
     * Generates default data set
     */
    public function createDefaultDataSet() {
        $count = $this->usersCount();
        $users = $this->createUsers($count);
        foreach ($users as $user) {
            $count = $this->businessesCount();
            $this->createBusinesses($user->id, $count);
        }

        $businesses = Business::find('all');

        foreach ($businesses as $business) {
            $count = $this->productsCount();
            $this->createProducts($business->id, $count);
        }

        $products = Product::find('all');
        foreach ($products as $product) {
            $count = $this->bookingsCount();
            $this->createBookings($product->id, $count);
        }
    }

    public function usersCount() {
        return mt_rand(5, 15);
    }

    public function businessesCount() {
        return mt_rand(0, 5);
    }

    public function productsCount() {
        return mt_rand(3, 8);
    }

    public function bookingsCount() {
        return mt_rand(2, 4);
    }
}
