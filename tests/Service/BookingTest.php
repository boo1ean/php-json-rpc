<?php
class BookingTest extends TestCase
{
    protected $p, $user, $product, $business, $booking;

    public function __construct() {
        parent::__construct();

        $time = new DateTime('NOW');
        $this->_p = array('start_time' => $time->format(DateTime::ISO8601));
    }

    public function setUp() {
        parent::setUp();
        $this->user     = $this->createUser();
        $this->business = $this->createBusiness($this->user->id);
        $this->product  = $this->createProduct($this->business->id);
        $this->booking  = $this->createBooking($this->product->id);
        $this->p = array_merge($this->_p, array(
            'user_id'    => $this->user->id,
            'booking_id' => $this->booking->id
        ));
    }

    public function testRequestBooking() {
        $this->assertNotEmpty($this->booking);

        $productBooking = $this->container['booking-service']->requestBooking($this->p);
        $this->assertNotEmpty($productBooking);
        $this->assertEquals($productBooking->user_id, $this->user->id);
        $this->assertEquals($productBooking->booking_id, $this->booking->id);
    }

    /**
     * @expectedException Exception
     */
    public function testRequestBookingInvalidUserId() {
        $p = $this->p;
        $p['user_id'] = '';
        $this->container['booking-service']->requestBooking($p);
    }

    /**
     * @expectedException Exception
     */
    public function testRequestBookingZeroUserId() {
        $p = $this->p;
        $p['user_id'] = 0;
        $this->container['booking-service']->requestBooking($p);
    }

    /**
     * @expectedException Exception
     */
    public function testRequestBookingNoBookingId() {
        $p = $this->p;
        unset($p['booking_id']);
        $this->container['booking-service']->requestBooking($p);
    }

    /**
     * @expectedException Exception
     */
    public function testRequestBookingInvalidBookingId() {
        $p = $this->p;
        $p['booking_id'] = 'id';
        $this->container['booking-service']->requestBooking($p);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidStartTimeFormat() {
        $time = new DateTime('NOW');
        $p = $this->p;
        $p['start_time'] = $time->format(DateTime::RFC850);

        $this->container['booking-service']->requestBooking($p);
    }

    /**
     * @expectedException Exception
     */
    public function testUserNotFound() {
        $p = $this->p;
        $p['user_id'] = 3948384;
        $this->container['booking-service']->requestBooking($p);
    }

    /**
     * @expectedException Exception
     */
    public function testBookingNotFound() {
        $p = $this->p;
        $p['booking_id'] = 3948384;
        $this->container['booking-service']->requestBooking($p);
    }
}
