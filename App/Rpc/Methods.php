<?php
namespace App\Rpc;
class Methods
{
    /**
     * DI container
     */
    protected $c;

    /**
     * @param Pimple $c DI container instance
     */
    public function __construct($c) {
        $this->c = $c;
    }

    public function login($p = array()) {
        $result = $this->c['user-service']->login($p);
        if (!$result) {
            throw new \Exception('Invalid email or password.');
        }

        return $this->c['user']->attributes();
    }

    /**
     * Get list of all businesses
     */
    public function businesses($p = array()) {
        $defaults = array(
            'rpp' => 20,
            'page' => 1,
            'include_reviews' => false
        );

        $p = array_merge($defaults, $p);

        $businesses = $this->c['business-service']->getBusinesses($p);

        $result = array();
        // TODO add aggregation method
        if ($p['include_reviews']) {
            foreach ($businesses as $business) {
                $reviews = $business->reviews;
                $reviewsData = array();
                foreach ($reviews as $review) {
                    $reviewsData[] = $review->attributes();
                }
                $data = $business->attributes();
                $data['reviews'] = $reviewsData;

                $result[] = $data;
            }
        } else {
            $result = $this->prepare($businesses);
        }

        return $result;
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

        $products = $this->c['product-service']->getProducts($p);

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

    /**
     * Request for booking
     */
    public function book($p) {
        $this->checkSession();
        $p['user_id'] = $this->c['user']->id;
        return $this->c['booking-service']->requestBooking($p)->attributes();
    }

    /**
     * Add review to a business
     */
    public function addReview($p) {
        $this->checkSession();
        $p['user_id'] = $this->c['user']->id;
        return $this->c['review-service']->addReview($p)->attributes();
    }

    /**
     * Destroy user session
     */
    public function logout() {
        $this->c['auth-service']->clearIdentity();
        return true;
    }


    /**
     * Serialize array of activerecords to array of objects
     *
     * @param array $models array of models
     * @return array of objects
     */
    protected function prepare($models = array()) {
        $result = array();
        foreach ($models as $model) {
            $result[] = $model->attributes();
        }

        return $result;
    }

    protected function checkSession() {
        if (!$this->c['user']) {
            throw new \Exception('Unauthorized.');
        }
    }
}
