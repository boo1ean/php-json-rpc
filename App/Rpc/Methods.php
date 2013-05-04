<?php
namespace App\Rpc;
class Methods
{
    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function login($p = array()) {
        $result = $this->container['user-service']->login($p);
        if (!$result) {
            throw new \Exception('Invalid email or password.');
        }

        return $this->container['user']->attributes();
    }

    /**
     * Get list of all businesses
     */
    public function businesses($p = array()) {
        $defaults = array(
            'rpp' => 20,
            'page' => 1
        );

        $p = array_merge($defaults, $p);

        $businesses = $this->container['business-service']->getBusinesses($p);
        return $this->prepare($businesses);
    }

    /**
     * Get list of product for specified business
     *
     * @param integer $business_id
     **/
    public function products($p = array()) {
        $defaults = array(
            'rpp' => 30,
            'page' => 1,
            'include_bookings' => false
        );

        $p = array_merge($defaults, $p);

        $products = $this->container['product-service']->getProducts($p);

        $result = array();
        if ($p['include_bookings']) {
            foreach ($products as $product) {
                $bookings = $product->bookings;
                $bookingsData = array();
                foreach ($bookings as $booking) {
                    $bookingsData[] = $booking->attributes();
                }

                $data = $product->attributes();
                $data['bookings'] = $bookingsData;

                $result[] = $data;
            }
        } else {
            $result = $this->prepare($products);
        }

        return $result;
    }

    public function logout() {
        $this->container['auth-service']->clearIdentity();
        return true;
    }

    /**
     * Serialize array of activerecords to array of objects
     *
     * @param array $models array of models
     * @return array of objects
     */
    public function prepare($models = array()) {
        $result = array();
        foreach ($models as $model) {
            $result[] = $model->attributes();
        }

        return $result;
    }
}
