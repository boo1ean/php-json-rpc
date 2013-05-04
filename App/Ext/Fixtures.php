<?php
namespace App\Ext;

use App\Model\User;
use App\Model\Business;
use App\Model\Product;

class Fixtures
{
    const DEFAULT_PASSWORD   = 'test';
    const DEFAULT_BATCH_SIZE = 10;

    public $methodPrefix = 'create';

    protected $faker;

    public function __construct() {
        $this->faker = \Faker\Factory::create();
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
        $users = $this->createUsers(30);
        foreach ($users as $user) {
            $count = $this->businessesCount();
            $this->createBusinesses($user->id, $count);
        }

        $businesses = Business::find('all');

        foreach ($businesses as $business) {
            $count = $this->productsCount();
            $this->createProducts($business->id, $count);
        }
    }

    public function businessesCount() {
        return mt_rand(0, 5);
    }

    public function productsCount() {
        return mt_rand(3, 8);
    }
}
