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
     * Request for a booking
     */
    public function book($p) {
        $p = $this->populateUserId($p);
        return $this->c['booking-service']->requestBooking($p)->attributes();
    }

    /**
     * Request for an order
     */
    public function order($p) {
        $p = $this->populateUserId($p);
        return $this->c['order-service']->requestOrder($p)->attributes();
    }

    /**
     * Add review to a business
     */
    public function addReview($p) {
        $p = $this->populateUserId($p);
        return $this->c['review-service']->addReview($p)->attributes();
    }

    /**
     * Destroy user session
     */
    public function logout() {
        $this->c['user-service']->logout();
        return true;
    }

    /**
     * Get status of specified product
     */
    public function productStatus($p) {
        return $this->c['product-service']->productStatus($p);
    }

    /**
     * Return product availability status at specified time for specified duration
     */
    public function isProductAvailable($p) {
        return $this->c['product-service']->isProductAvailable($p);
    }

    /**
     * Get list of pending bookigs for current user
     */
    public function pendingBookings($p = array()) {
        $p = $this->populateUserId($p);
        return $this->c['booking-service']->pendingBookings($p);
    }

    /**
     * Get list of pending orders for current user
     */
    public function pendingOrders($p = array()) {
        $p = $this->populateUserId($p);
        return $this->c['order-service']->pendingOrders($p);
    }

    /**
     * Approve product order
     */
    public function approveOrder($p) {
        $p = $this->populateUserId($p);
        $p['status'] = \App\Model\ProductOrder::APPROVED;
        return $this->c['order-service']->setOrderStatus($p)->attributes();
    }

    /**
     * Reject product order
     */
    public function rejectOrder($p) {
        $p = $this->populateUserId($p);
        $p['status'] = \App\Model\ProductOrder::REJECTED;
        return $this->c['order-service']->setOrderStatus($p)->attributes();
    }

    /**
     * Approve bookings product
     */
    public function approveBooking($p) {
        $p = $this->populateUserId($p);
        $p['status'] = \App\Model\ProductBooking::APPROVED;
        return $this->c['booking-service']->setBookingStatus($p)->attributes();
    }

    /**
     * Reject bookings product
     */
    public function rejectBooking($p) {
        $p = $this->populateUserId($p);
        $p['status'] = \App\Model\ProductBooking::REJECTED;
        return $this->c['booking-service']->setBookingStatus($p)->attributes();
    }

    /**
     * Cancel booking request
     */
    public function cancelBooking($p) {
        $p = $this->populateUserId($p);
        $p['status'] = \App\Model\ProductBooking::CANCELED;
        return $this->c['booking-service']->setBookingStatus($p)->attributes();
    }

    public function addDevice($p) {
        $p = $this->populateUserId($p);
        return $this->c['user-service']->addDevice($p)->attributes();
    }

    /**
     * Cancel order request
     */
    public function cancelOrder($p) {
        $p = $this->populateUserId($p);
        $p['status'] = \App\Model\ProductOrder::CANCELED;
        return $this->c['order-service']->setOrderStatus($p)->attributes();
    }

    /**
     * Check for updates since passed date
     */
    public function checkForUpdates($p) {
        return $this->c['business-service']->checkForUpdates($p);
    }

    /**
     * Get top 10 businesses
     */
    public function topBusinesses() {
        return $this->c['business-service']->topBusinesses();
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

    protected function populateUserId($p) {
        $user = $this->c['user'];
        if (!$user) {
            throw new \Exception('Unauthorized.');
        }

        $p['user_id'] = $user->id;
        return $p;
    }

    // For test purposes
    // @codeCoverageIgnoreStart
    public function createUser($p) {
        return \App\Model\User::create($p)->attributes();
    }

    public function createBusiness($p) {
        return \App\Model\Business::create($p)->attributes();
    }

    public function createProduct($p) {
        return \App\Model\Product::create($p)->attributes();
    }

    public function createBooking($p) {
        return \App\Model\Booking::create($p)->attributes();
    }

    public function createProductBooking($p) {
        return \App\Model\ProductBooking::create($p)->attributes();
    }

    public function createReview($p) {
        return \App\Model\Review::create($p)->attributes();
    }

    public function createDevice($p) {
        return \App\Model\Device::create($p)->attributes();
    }
}
