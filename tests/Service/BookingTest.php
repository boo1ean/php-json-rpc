<?php
class BookingTest extends TestCase
{
    protected $p, $user, $product, $business, $booking;

    public function __construct() {
        parent::__construct();

        $time = new DateTime('NOW');
        $time->add(new DateInterval('PT1M'));
        $this->_p = array('start_time' => $time->format($this->container['config']['date_format']));
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

    /**
     * @expectedException Exception
     */
    public function testBookingInPast() {
        $p = $this->p;
        $p['start_time'] = date_create()->format($this->container['config']['date_format']);
        $this->container['booking-service']->requestBooking($p);
    }

    public function testGetPendingBookings() {
        $p = array('user_id' => $this->user->id);
        $bookings = $this->container['booking-service']->pendingBookings($p);
        $this->assertInternalType('array', $bookings);
        $this->assertEmpty($bookings);

        $user = $this->createUser();
        $this->createProductBooking($user->id, $this->booking->id);
        $bookings = $this->container['booking-service']->pendingBookings($p);
        $this->assertCount(1, $bookings);

        $this->createProductBooking($user->id, $this->booking->id);
        $this->createProductBooking($user->id, $this->booking->id);
        $bookings = $this->container['booking-service']->pendingBookings($p);
        $this->assertCount(3, $bookings);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidUserPendingBookings() {
        $p = array('user_id' => 345345);
        $bookings = $this->container['booking-service']->pendingBookings($p);
    }

    /**
     * @expectedException Exception
     */
    public function testRequestBookingUnavailable() {
        $this->createProductBooking($this->user->id, $this->booking->id, array(
            'start_time' => $this->p['start_time']
        ));
        $this->container['booking-service']->requestBooking($this->p);
    }

    public function testSetBookingStatus(){
        $user = $this->createUser();
        $pb   = $this->createProductBooking($user->id, $this->booking->id);

        $status = \App\Model\ProductBooking::REJECTED;
        $p = array(
            'product_booking_id' => $pb->id,
            'user_id'            => $this->user->id,
            'status'             => $status
        );

        $result = $this->container['booking-service']->setBookingStatus($p);
        $this->assertEquals($result->status, $status);

        $status = \App\Model\ProductBooking::APPROVED;
        $p['status'] = $status;

        $result = $this->container['booking-service']->setBookingStatus($p);
        $this->assertEquals($result->status, $status);
    }

    /**
     * @expectedException Exception
     */
    public function testSetBookingStatusInvalidUserId() {
        $user = $this->createUser();
        $pb   = $this->createProductBooking($user->id, $this->booking->id);

        $status = \App\Model\ProductBooking::REJECTED;
        $p = array(
            'product_booking_id' => $pb->id,
            'user_id'            => 2342423,
            'status'             => $status
        );

        $result = $this->container['booking-service']->setBookingStatus($p);
    }

    /**
     * @expectedException Exception
     */
    public function testSetBookingStatusInvalidProductBookingId() {
        $user = $this->createUser();
        $pb   = $this->createProductBooking($user->id, $this->booking->id);

        $status = \App\Model\ProductBooking::REJECTED;
        $p = array(
            'product_booking_id' => 2432342,
            'user_id'            => $this->user->id,
            'status'             => $status
        );

        $result = $this->container['booking-service']->setBookingStatus($p);
    }

    /**
     * @expectedException Exception
     */
    public function testSetBookingStatusInvalidStatus() {
        $user = $this->createUser();
        $pb   = $this->createProductBooking($user->id, $this->booking->id);

        $p = array(
            'product_booking_id' => $pb->id,
            'user_id'            => $this->user->id,
            'status'             => 'wtf'
        );

        $result = $this->container['booking-service']->setBookingStatus($p);
    }

    /**
     * @expectedException Exception
     */
    public function testSetBookingStatusDontHavePermissions() {
        $user = $this->createUser();
        $pb   = $this->createProductBooking($user->id, $this->booking->id);

        $status = \App\Model\ProductBooking::REJECTED;
        $p = array(
            'product_booking_id' => $pb->id,
            'user_id'            => $user->id,
            'status'             => $status
        );

        $result = $this->container['booking-service']->setBookingStatus($p);
    }

}
