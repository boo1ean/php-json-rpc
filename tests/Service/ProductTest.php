<?php
class ProductService extends TestCase
{
    public $p = array(
        'rpp'              => 30,
        'page'             => 1,
        'business_id'      => 1,
        'include_bookings' => ''
    );

    public function testGetProducts() {
        $count    = 10;
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);
        $products = $this->createProducts($business->id, $count);

        $p = array_merge($this->p, array('business_id' => $business->id));
        $result = $this->container['product-service']->getProducts($p);
        $this->assertCount($count, $result);
    }

    public function testNoProducts() {
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);
        $p = array_merge($this->p, array('business_id' => $business->id));
        $result = $this->container['product-service']->getProducts($p);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidBusinessId() {
        $p = array_merge($this->p, array('business_id' => 'custom'));
        $this->container['product-service']->getProducts($p);
    }

    /**
     * @expectedException Exception
     */
    public function testNoParams() {
        $this->container['product-service']->getProducts();
    }

    /**
     * @expectedException Exception
     */
    public function testEmptyBusinessId() {
        $p = array('business_id' => '');
        $this->container['product-service']->getProducts($p);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidPage() {
        $p = $this->p;
        $p['page'] = '';
        $this->container['product-service']->getProducts($p);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidMaxRpp() {
        $p = $this->p;
        $p['rpp'] = 31;
        $this->container['product-service']->getProducts($p);
    }

    public function testProductWithBookings() {
        $p = $this->p;
        $p['include_bookings'] = true;

        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);
        $products = $this->createProducts($business->id, 3);
        foreach ($products as $product) {
            $this->createBookings($product->id, 3);
        }

        $prdoucts = $this->container['product-service']->getProducts($p);
        $this->assertCount(3, $products);
        foreach ($products as $product) {
            $this->assertCount(3, $product->bookings);
        }
    }

    public function testProductStatus() {
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);
        $product  = $this->createProduct($business->id);
        $booking  = $this->createBooking($product->id);

        $time = new DateTime('NOW');
        $time->add(new DateInterval('P1M'));
        $p = array(
            'user_id'    => $user->id,
            'booking_id' => $booking->id,
            'start_time' => $time->format(DateTime::ISO8601)
        );

        $bookingProduct = $this->container['booking-service']->requestBooking($p);
        $this->assertNotEmpty($bookingProduct);

        $time->add(new DateInterval('P1M'));
        $p['start_time'] = $time->format(DateTime::ISO8601);
        $bookingProduct = $this->container['booking-service']->requestBooking($p);
        $this->assertNotEmpty($bookingProduct);

        $status = $this->container['product-service']->productStatus(array('product_id' => $product->id));
        $this->assertNotEmpty($status);
        $this->assertInternalType('array', $status);
        $this->assertCount(2, $status);

        $this->assertEquals($status[0]['duration'], $booking->duration);
        $this->assertEquals($status[1]['duration'], $booking->duration);
    }

    /**
     * @expectedException Exception
     */
    public function testProductNotFound() {
        $p = array('product_id' => 3948384);
        $this->container['product-service']->productStatus($p);
    }

}
