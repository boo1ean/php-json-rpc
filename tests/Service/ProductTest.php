<?php
class ProductService extends TestCase
{
    private $user, $business, $product, $booking;

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
        $p = $this->p;
        $p['business_id'] = 'custom';
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
        $this->prepareBooking();

        $time = new DateTime('NOW');
        $time->add(new DateInterval('P1M'));
        $p = array(
            'user_id'    => $this->user->id,
            'booking_id' => $this->booking->id,
            'start_time' => $time->format($this->container['config']['date_format'])
        );

        $bookingProduct = $this->container['booking-service']->requestBooking($p);
        $this->assertNotEmpty($bookingProduct);

        $time->add(new DateInterval('P1M'));
        $p['start_time'] = $time->format($this->container['config']['date_format']);
        $bookingProduct = $this->container['booking-service']->requestBooking($p);
        $this->assertNotEmpty($bookingProduct);

        $status = $this->container['product-service']->productStatus(array('product_id' => $this->product->id));
        $this->assertNotEmpty($status);
        $this->assertInternalType('array', $status);
        $this->assertCount(2, $status);

        $this->assertEquals($status[0]['duration'], $this->booking->duration);
        $this->assertEquals($status[1]['duration'], $this->booking->duration);
    }

    /**
     * @expectedException Exception
     */
    public function testProductNotFound() {
        $p = array('product_id' => 3948384);
        $this->container['product-service']->productStatus($p);
    }

    /**
     * @expectedException Exception
     */
    public function testIsProductAvailableInPast() {
        $p = array(
            'product_id' => 1,
            'product_id' => 100,
            'start_time' => date_create()->format($this->container['config']['date_format'])
        );

        $this->container['product-service']->isProductAvailable($p);
    }

    public function testIsProductAvailable() {
        $this->prepareBooking();
        $time = date_create()
            ->add(new DateInterval('P1M'))
            ->format($this->container['config']['date_format']);

        $p = array(
            'product_id' => $this->product->id,
            'booking_id' => $this->booking->id,
            'start_time' => $time
        );

        $result = $this->container['product-service']->isProductAvailable($p);
        $this->assertTrue($result);

        $this->createProductBooking($this->user->id, $this->booking->id, array(
            'start_time' => $time
        ));

        $result = $this->container['product-service']->isProductAvailable($p);
        $this->assertFalse($result);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidBookingIsAvailable() {
        $this->prepareBooking();
        $time = date_create()
            ->add(new DateInterval('P1M'))
            ->format($this->container['config']['date_format']);

        $p = array(
            'product_id' => $this->product->id,
            'booking_id' => $this->booking->id + 1,
            'start_time' => $time
        );

        $this->container['product-service']->isProductAvailable($p);
    }

    private function prepareBooking() {
        $this->user     = $this->createUser();
        $this->business = $this->createBusiness($this->user->id);
        $this->product  = $this->createProduct($this->business->id);
        $this->booking  = $this->createBooking($this->product->id);
    }

    public function testAllProducts() {
        $p = $this->p;
        unset($p['business_id']);
        $user = $this->createUser();
        $business = $this->createBusiness($user->id);
        $this->createProducts($business->id, 10);

        $business = $this->createBusiness($user->id);
        $this->createProducts($business->id, 9);

        $result = $this->container['product-service']->getProducts($p);
        $this->assertCount(19, $result);
    }
}
