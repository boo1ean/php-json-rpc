<?php
class OrderTest extends TestCase
{
    protected $p, $user, $product, $business, $order;

    public function setUp() {
        parent::setUp();
        $this->user     = $this->createUser();
        $this->business = $this->createBusiness($this->user->id);
        $this->product  = $this->createProduct($this->business->id);
        $this->p = array(
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id
        );
    }

    public function testRequestOrder() {
        $order = $this->container['order-service']->requestOrder($this->p);
        $this->assertNotEmpty($order);
        $this->assertEquals($order->user_id, $this->user->id);
        $this->assertEquals($order->product_id, $this->product->id);
        $this->assertEquals($order->status, App\Model\ProductOrder::PENDING);
    }

    /**
     * @expectedException Exception
     */
    public function testRequestOrderUnavailableProduct() {
        $this->product->status = App\Model\Product::SOLD;
        $this->product->save();
        $order = $this->container['order-service']->requestOrder($this->p);
    }

    /**
     * @expectedException Exception
     */
    public function testRequestOrderInvalidUserId() {
        $p = $this->p;
        $p['user_id'] = '';
        $this->container['order-service']->requestOrder($p);
    }

    /**
     * @expectedException Exception
     */
    public function testRequestOrderZeroUserId() {
        $p = $this->p;
        $p['user_id'] = 0;
        $this->container['order-service']->requestOrder($p);
    }

    /**
     * @expectedException Exception
     */
    public function testRequestOrderNoProductId() {
        $p = $this->p;
        unset($p['product_id']);
        $this->container['order-service']->requestOrder($p);
    }

    /**
     * @expectedException Exception
     */
    public function testRequestBookingInvalidProductId() {
        $p = $this->p;
        $p['product_id'] = 'id';
        $this->container['order-service']->requestOrder($p);
    }

    /**
     * @expectedException Exception
     */
    public function testUserNotFound() {
        $p = $this->p;
        $p['user_id'] = 3948384;
        $this->container['order-service']->requestOrder($p);
    }

    /**
     * @expectedException Exception
     */
    public function testProductNotFound() {
        $p = $this->p;
        $p['product_id'] = 3948384;
        $this->container['order-service']->requestOrder($p);
    }

    public function testGetPendingOrders() {
        $p = array('user_id' => $this->user->id);
        $orders = $this->container['order-service']->pendingOrders($p);
        $this->assertInternalType('array', $orders);
        $this->assertEmpty($orders);

        $user = $this->createUser();
        $this->createProductOrder($user->id, $this->product->id);
        $orders = $this->container['order-service']->pendingOrders($p);
        $this->assertCount(1, $orders);

        $this->createProductOrder($user->id, $this->product->id);
        $this->createProductOrder($user->id, $this->product->id);
        $orders = $this->container['order-service']->pendingOrders($p);
        $this->assertCount(3, $orders);

        $this->createProductOrder($user->id, $this->product->id, array(
            'status' => App\Model\ProductOrder::APPROVED
        ));

        $orders = $this->container['order-service']->pendingOrders($p);
        $this->assertCount(3, $orders);

        $this->createProductOrder($user->id, $this->product->id, array(
            'status' => App\Model\ProductOrder::REJECTED
        ));

        $orders = $this->container['order-service']->pendingOrders($p);
        $this->assertCount(3, $orders);
    }

    public function testPendingOrderAnotherUsersBusiness() {
        $user = $this->createUser();
        $p = array('user_id' => $user->id);
        $orders = $this->container['order-service']->pendingOrders($p);
        $this->assertInternalType('array', $orders);
        $this->assertEmpty($orders);

        $this->createProductOrder($this->user->id, $this->product->id);
        $this->createProductOrder($this->user->id, $this->product->id);

        $orders = $this->container['order-service']->pendingOrders($p);
        $this->assertEmpty($orders);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidUserPendingBookings() {
        $p = array('user_id' => 345345);
        $bookings = $this->container['order-service']->pendingOrders($p);
    }

    //public function testSetBookingStatus() {
        //$user = $this->createUser();
        //$pb   = $this->createProductBooking($user->id, $this->order->id);

        //$status = \App\Model\ProductBooking::REJECTED;
        //$p = array(
            //'product_booking_id' => $pb->id,
            //'user_id'            => $this->user->id,
            //'status'             => $status
        //);

        //$result = $this->container['order-service']->setBookingStatus($p);
        //$this->assertEquals($result->status, $status);

        //$status = \App\Model\ProductBooking::APPROVED;
        //$p['status'] = $status;

        //$result = $this->container['order-service']->setBookingStatus($p);
        //$this->assertEquals($result->status, $status);
    //}

    /**
     * @expectedException Exception
     */
    //public function testSetBookingStatusInvalidUserId() {
        //$user = $this->createUser();
        //$pb   = $this->createProductBooking($user->id, $this->order->id);

        //$status = \App\Model\ProductBooking::REJECTED;
        //$p = array(
            //'product_booking_id' => $pb->id,
            //'user_id'            => 2342423,
            //'status'             => $status
        //);

        //$result = $this->container['order-service']->setBookingStatus($p);
    //}

    /**
     * @expectedException Exception
     */
    //public function testSetBookingStatusProductBookingId() {
        //$user = $this->createUser();
        //$pb   = $this->createProductBooking($user->id, $this->order->id);

        //$status = \App\Model\ProductBooking::REJECTED;
        //$p = array(
            //'product_booking_id' => 2432342,
            //'user_id'            => $this->user->id,
            //'status'             => $status
        //);

        //$result = $this->container['order-service']->setBookingStatus($p);
    //}

    /**
     * @expectedException Exception
     */
    //public function testSetBookingStatusInvalidStatus() {
        //$user = $this->createUser();
        //$pb   = $this->createProductBooking($user->id, $this->order->id);

        //$p = array(
            //'product_booking_id' => $pb->id,
            //'user_id'            => $this->user->id,
            //'status'             => 'wtf'
        //);

        //$result = $this->container['order-service']->setBookingStatus($p);
    //}

    /**
     * @expectedException Exception
     */
    //public function testSetBookingStatusDontHavePermissions() {
        //$user = $this->createUser();
        //$pb   = $this->createProductBooking($user->id, $this->order->id);

        //$status = \App\Model\ProductBooking::REJECTED;
        //$p = array(
            //'product_booking_id' => $pb->id,
            //'user_id'            => $user->id,
            //'status'             => $status
        //);

        //$result = $this->container['order-service']->setBookingStatus($p);
    //}

}
