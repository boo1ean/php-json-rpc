<?php
namespace App\Ext;

use App\Model\User;
use App\Model\Business;
use App\Model\Product;
use App\Model\Booking;
use App\Model\ProductBooking;
use App\Model\Review;

// @codeCoverageIgnoreStart
class Fixtures
{
    const DEFAULT_PASSWORD   = 'test';
    const DEFAULT_BUNCH_SIZE = 10;

    public $methodPrefix = 'create';

    protected $faker;
    protected $silent;

    public function __construct($silent = true) {
        $this->faker  = \Faker\Factory::create();
        $this->silent = $silent;
        $this->date_format = \DateTime::W3C;
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
    public function createUser($p = array()) {
        $p = array_merge(array(
            'email'        => $this->faker->email,
            'password'     => self::DEFAULT_PASSWORD,
            'first_name'   => $this->faker->firstName,
            'last_name'    => $this->faker->lastName,
            'country'      => $this->faker->country,
            'phone_number' => $this->faker->phoneNumber,
            'city'         => $this->faker->city,
            'address'      => $this->faker->address
        ), $p);

        $this->log("User created: {$p['first_name']} {$p['last_name']} {$p['email']}");
        return User::create($p);
    }

    public function createUsers($count = self::DEFAULT_BUNCH_SIZE) {
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
    public function createBusinesses($user_id, $count = self::DEFAULT_BUNCH_SIZE) {
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
            'price'       => $this->faker->randomFloat
        ), $p);

        $this->log("Product created: business_id={$p['business_id']}, {$p['name']}");
        return Product::create($p);
    }

    /**
     * Create review
     *
     * @param integer $user_id
     * @param integer $business_id
     */
    public function createReview($user_id, $business_id, $p = array()) {
        $p = array_merge(array(
            'business_id' => $business_id,
            'user_id'     => $user_id,
            'title'       => $this->faker->name,
            'body'        => $this->faker->text
        ), $p);

        $this->log("Review created: business_id={$p['business_id']}, user_id={$p['user_id']}");
        return Review::create($p);
    }

    /**
     * Create bunch of reviews
     *
     * @param integer $user_id
     * @param integer $business_id
     * @@aram integer $count number of reviews to create
     */
    public function createReviews($user_id, $business_id, $count = self::DEFAULT_BUNCH_SIZE) {
        $reviews = array();
        for ($i = 0; $i < $count; ++$i) {
            $reviews[] = $this->createReview($user_id, $business_id);
        }

        return $reviews;
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
    public function createBookings($product_id, $count = self::DEFAULT_BUNCH_SIZE) {
        $bookings = array();
        for ($i = 0; $i < $count; ++$i) {
            $bookings[] = $this->createBooking($product_id);
        }

        return $bookings;
    }

    /**
     * Create single product booking record
     *
     * @param $user_id
     * @param $booking_id
     */
    public function createProductBooking($user_id, $booking_id, $p = array()) {
        $time = $this->faker->dateTimeThisMonth;
        $time->add(new \DateInterval('P1M'));
        $p = array_merge(array(
            'user_id'    => $user_id,
            'booking_id' => $booking_id,
            'start_time' => $time->format($this->date_format)
        ), $p);

        $this->log("ProductBooking created: booking_id={$p['booking_id']} user_id{$p['user_id']} {$p['start_time']}");
        return ProductBooking::create($p);
    }

    /**
     * Create bunch of products for specified business
     *
     * @param integer $business_id
     * @param integer $count number of products
     */
    public function createProducts($business_id, $count = self::DEFAULT_BUNCH_SIZE) {
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

        $user = User::first();
        $products = Product::find('all');
        foreach ($products as $product) {
            $count    = $this->bookingsCount();
            $bookings = $this->createBookings($product->id, $count);

            $this->createProductBooking($user->id, $bookings[0]->id);
            $this->createProductBooking($user->id, $bookings[1]->id);
            $this->createProductBooking($user->id, $bookings[2]->id);
        }
    }

    public function usersCount() {
        return mt_rand(3, 5);
    }

    public function businessesCount() {
        return mt_rand(0, 2);
    }

    public function productsCount() {
        return mt_rand(3, 8);
    }

    public function bookingsCount() {
        return mt_rand(3, 4);
    }
}
