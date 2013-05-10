<?php

use App\Model\User;
class MethodsTest extends TestCase
{
    protected $server;
    protected $request;

    public function __construct() {
        parent::__construct();
        $this->server  = $this->container['json-rpc-server'];
        $this->request = array(
            'jsonrpc' => '2.0',
            'method'  => 'login',
            'params'  => array(),
            'id'      => $this->faker->md5
        );
    }

    public function testLogin() {
        $params = array(
            'email'    => $this->faker->email,
            'password' => $this->faker->phoneNumber
        );

        User::create($params);
        $request = $this->composeRequest(array(
            'method' => 'login',
            'params' => $params
        ));

        $response = $this->server->handleRequest($request);
        $this->assertNotEmpty($response);

        $response = json_decode($response);
        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->result);
        $this->assertEquals($request->id, $response->id);

        $user = $this->container['user'];
        $this->assertEquals($user->email, $params['email']);
    }

    public function testInvalidLogin() {
        $params = array(
            'email'    => $this->faker->md5,
            'password' => $this->faker->md5
        );

        $request = $this->composeRequest(array(
            'method' => 'login',
            'params' => $params
        ));

        // Invalid email
        $response = $this->server->handleRequest($request);
        $response = json_decode($response);
        $this->assertObjectHasAttribute('error', $response);

        $params['email'] = $this->faker->email; 
        $request = $this->composeRequest(array(
            'method' => 'login',
            'params' => $params
        ));

        // Invalid credentials
        $response = $this->server->handleRequest($request);
        $response = json_decode($response);
        $this->assertObjectHasAttribute('error', $response);
    }

    public function testInvalidLoginParams() {
        $params = array(
            'email' => $this->faker->md5
        );

        $request = $this->composeRequest(array(
            'method' => 'login',
            'params' => $params
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);
        $this->assertObjectHasAttribute('error', $response);

        $params = array(
            'password' => $this->faker->md5
        );

        $request = $this->composeRequest(array(
            'method' => 'login',
            'params' => $params
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);
        $this->assertObjectHasAttribute('error', $response);
    }

    public function testNoBusinesses() {
        $request = $this->composeRequest(array(
            'method' => 'businesses',
            'params' => array(
                'rpp'  => 20,
                'page' => 1
            )
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('result', $response);
        $result = $response->result;
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function testBusinessesWithReviews() {
        $reviewsCount = 12;
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);
        $reviews  = $this->createReviews($user->id, $business->id, $reviewsCount);

        $params = array(
            'include_reviews' => true
        );

        $request = $this->composeRequest(array(
            'method' => 'businesses',
            'params' => $params
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('result', $response);
        $businesses = $response->result;
        $this->assertInternalType('array', $businesses);
        $reviews = $businesses[0]->reviews;
        $this->assertInternalType('array', $reviews);
        $this->assertCount($reviewsCount, $reviews);
    }

    public function testProductsWithBookings() {
        $bookingsCount = 12;
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);
        $product  = $this->createProduct($business->id);
        $bookings = $this->createBookings($product->id, $bookingsCount);

        $params = array(
            'include_bookings' => true,
            'business_id'      => $business->id
        );

        $request = $this->composeRequest(array(
            'method' => 'products',
            'params' => $params
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('result', $response);
        $businesses = $response->result;
        $this->assertInternalType('array', $businesses);
        $bookings = $businesses[0]->bookings;
        $this->assertInternalType('array', $bookings);
        $this->assertCount($bookingsCount, $bookings);
    }

    public function testAddDevice() {
        $user = $this->createUser();

        $type = App\Model\Device::IOS;
        $params = array(
            'type'  => $type,
            'token' => $this->faker->md5
        );

        $request = $this->composeRequest(array(
            'method' => 'addDevice',
            'params' => $params
        ));

        $this->container['user'] = $user;

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('result', $response);
        $device = $response->result;
        $this->assertEquals($device->type, $type);
        $this->assertEquals($device->user_id, $user->id);
    }

    public function testCheckForUpdates() {
        $user = $this->createUser();
        $business = $this->createBusiness($user->id);
        $time = new \DateTime('NOW');

        $params = array(
            'business_id' => $business->id,
            'time'        => $time->format($this->container['config']['date_format'])
        );

        $request = $this->composeRequest(array(
            'method' => 'checkForUpdates',
            'params' => $params
        ));

        $this->createProducts($business->id, 3);

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('result', $response);

        $updatesCount = $response->result->updates_count;
        $this->assertEquals(3, $updatesCount);
    }

    public function testAddDeviceUnauthorized() {
        $user = $this->createUser();

        $type = App\Model\Device::IOS;
        $params = array(
            'type'  => $type,
            'token' => $this->faker->md5
        );

        $request = $this->composeRequest(array(
            'method' => 'addDevice',
            'params' => $params
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('error', $response);
    }

    public function testAddReview() {
        $bookingsCount = 12;
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);

        $params = array(
            'business_id' => $business->id,
            'title'       => $this->faker->name,
            'body'        => $this->faker->text
        );

        $request = $this->composeRequest(array(
            'method' => 'addReview',
            'params' => $params
        ));

        $this->container['user'] = $user;

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('result', $response);
        $this->assertEquals($response->result->business_id, $business->id);
        $this->assertEquals($response->result->user_id, $user->id);
    }

    public function testUnauthorizedCall() {
        $params = array(
            'business_id' => 34,
            'title'       => $this->faker->name,
            'body'        => $this->faker->text
        );

        $request = $this->composeRequest(array(
            'method' => 'addReview',
            'params' => $params
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('error', $response);
        $this->assertEquals($response->error->message, 'Unauthorized.');
    }

    public function testLogout() {
        $user = $this->createUser(array(
            'password' => 'test'
        ));

        $params = array(
            'email'    => $user->email,
            'password' => 'test'
        );

        $request = $this->composeRequest(array(
            'method' => 'login',
            'params' => $params
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('result', $response);
        $this->assertEquals($user->id, $response->result->id);

        $identity = $this->container['auth-service']->getIdentity();
        $this->assertEquals($user->id, $identity->id);

        $request = $this->composeRequest(array(
            'method' => 'logout'
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertTrue($response->result);
        $this->assertNull($this->container['auth-service']->getIdentity());
    }

    public function testBusinessProducts() {
        $count    = 12;
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);

        $this->createProducts($business->id, $count);

        $params = array(
            'business_id' => $business->id
        );

        $request = $this->composeRequest(array(
            'method' => 'products',
            'params' => $params
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('result', $response);
        $result = $response->result;
        $this->assertCount($count, $result);

        $this->createProducts($business->id, $count);
        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('result', $response);
        $result = $response->result;
        $this->assertCount($count * 2, $result);

    }

    public function testBusinessesList() {
        $count = 12;
        $user = $this->createUser();
        $this->createBusinesses($user->id, $count);

        $request = $this->composeRequest(array(
            'method' => 'businesses',
            'params' => array(
                'rpp'  => 20,
                'page' => 1
            )
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('result', $response);
        $result = $response->result;
        $this->assertCount($count, $result);
    }

    public function testBookMethod() {
        $this->prepareBooking();

        $time = new \DateTime('NOW');
        $time->add(new \DateInterval("PT1M"));
        $params = array(
            'booking_id' => $this->booking->id,
            'start_time' => $time->format($this->container['config']['date_format'])
        );

        $request = $this->composeRequest(array(
            'method' => 'book',
            'params' => $params
        ));

        // Test unauthorized
        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('error', $response);
        $this->container['user'] = $this->user;

        $request = $this->composeRequest(array(
            'method' => 'book',
            'params' => $params
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('result', $response);
        $productBooking = $response->result;

        $this->assertEquals($productBooking->user_id, $this->user->id);
        $this->assertEquals($productBooking->booking_id, $this->booking->id);
    }

    public function testOrderMethod() {
        $this->prepareBooking();

        $params = array(
            'product_id' => $this->product->id
        );

        $request = $this->composeRequest(array(
            'method' => 'order',
            'params' => $params
        ));

        // Test unauthorized
        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('error', $response);
        $this->container['user'] = $this->user;

        $request = $this->composeRequest(array(
            'method' => 'order',
            'params' => $params
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('result', $response);
        $order = $response->result;

        $this->assertEquals($order->user_id, $this->user->id);
        $this->assertEquals($order->product_id, $this->product->id);
    }

    public function testPendingBookings() {
        $this->prepareBooking();

        $user = $this->createUser();
        $time = date_create('NOW')->add(new \DateInterval('P1M'));
        $this->createProductBooking($user->id, $this->booking->id);
        $this->createProductBooking($user->id, $this->booking->id);

        $request = $this->composeRequest(array(
            'method' => 'pendingBookings'
        ));

        $this->container['user'] = $this->user;
        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('result', $response);
        $bookings = $response->result;
        $this->assertCount(2, $bookings);
    }

    public function testPendingOrders() {
        $this->prepareBooking();

        $user = $this->createUser();
        $this->createProductOrder($user->id, $this->product->id);
        $this->createProductOrder($user->id, $this->product->id);

        $request = $this->composeRequest(array(
            'method' => 'pendingOrders'
        ));

        $this->container['user'] = $this->user;
        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('result', $response);
        $bookings = $response->result;
        $this->assertCount(2, $bookings);
    }

    public function testNoPendingBookings() {
        $this->prepareBooking();

        $user = $this->createUser();
        $this->createProductBooking($user->id, $this->booking->id);
        $this->createProductBooking($user->id, $this->booking->id);

        $request = $this->composeRequest(array(
            'method' => 'pendingBookings'
        ));

        $this->container['user'] = $user;
        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('result', $response);
        $bookings = $response->result;
        $this->assertCount(0, $bookings);
    }

    public function testPendingBookingUnauthorized() {
        $request = $this->composeRequest(array(
            'method' => 'pendingBookings'
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);
        $this->assertObjectHasAttribute('error', $response);
    }

    public function testIsProductAvailable() {
        $this->prepareBooking();
        $time = date_create()
            ->add(new DateInterval('P1M'))
            ->format($this->container['config']['date_format']);

        $params = array(
            'product_id' => $this->product->id,
            'booking_id' => $this->booking->id,
            'start_time' => $time
        );

        $request = $this->composeRequest(array(
            'method' => 'isProductAvailable',
            'params' => $params
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertTrue($response->result);
    }

    public function testRpcInterface() {
        $method = $this->container['rpc-methods'];

        $this->assertTrue(is_callable(array($method, 'login')));
        $this->assertTrue(is_callable(array($method, 'logout')));
        $this->assertTrue(is_callable(array($method, 'products')));
        $this->assertTrue(is_callable(array($method, 'businesses')));
        $this->assertTrue(is_callable(array($method, 'addReview')));
        $this->assertTrue(is_callable(array($method, 'book')));
        $this->assertTrue(is_callable(array($method, 'order')));
        $this->assertTrue(is_callable(array($method, 'productStatus')));
        $this->assertTrue(is_callable(array($method, 'isProductAvailable')));
        $this->assertTrue(is_callable(array($method, 'pendingBookings')));
        $this->assertTrue(is_callable(array($method, 'pendingOrders')));
        $this->assertTrue(is_callable(array($method, 'approveBooking')));
        $this->assertTrue(is_callable(array($method, 'rejectBooking')));
        $this->assertTrue(is_callable(array($method, 'approveOrder')));
        $this->assertTrue(is_callable(array($method, 'rejectOrder')));
        $this->assertTrue(is_callable(array($method, 'cancelBooking')));
        $this->assertTrue(is_callable(array($method, 'cancelOrder')));
        $this->assertTrue(is_callable(array($method, 'addDevice')));

        $this->assertFalse(is_callable(array($method, 'prepare')));
        $this->assertFalse(is_callable(array($method, 'populateUserId')));
    }

    public function testApproveBooking() {
        $this->prepareBooking();
        $user = $this->createUser();
        $productBooking = $this->createProductBooking($user->id, $this->booking->id);

        $params = array(
            'product_booking_id' => $productBooking->id
        );

        $request = $this->composeRequest(array(
            'method' => 'approveBooking',
            'params' => $params
        ));

        $this->container['user'] = $this->user;

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertNotEmpty($response);
        $this->assertObjectHasAttribute('result', $response);

        $pb = $response->result;
        $this->assertEquals($pb->status, \App\Model\ProductBooking::APPROVED);
    }

    public function testApproveOrder() {
        $this->prepareBooking();
        $user  = $this->createUser();
        $order = $this->createProductOrder($user->id, $this->product->id);

        $params = array(
            'product_order_id' => $order->id
        );

        $request = $this->composeRequest(array(
            'method' => 'approveOrder',
            'params' => $params
        ));

        $this->container['user'] = $this->user;

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertNotEmpty($response);
        $this->assertObjectHasAttribute('result', $response);

        $pb = $response->result;
        $this->assertEquals($pb->status, \App\Model\ProductOrder::APPROVED);
    }

    public function testCancelBooking() {
        $this->prepareBooking();
        $user = $this->createUser();
        $pb   = $this->createProductBooking($user->id, $this->booking->id);

        $params = array(
            'product_booking_id' => $pb->id
        );

        $request = $this->composeRequest(array(
            'method' => 'cancelBooking',
            'params' => $params
        ));

        $this->container['user'] = $user;

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertNotEmpty($response);
        $this->assertObjectHasAttribute('result', $response);

        $pb = $response->result;
        $this->assertEquals($pb->status, \App\Model\ProductBooking::CANCELED);
    }

    public function testCancelOrder() {
        $this->prepareBooking();
        $user  = $this->createUser();
        $order = $this->createProductOrder($user->id, $this->product->id);

        $params = array(
            'product_order_id' => $order->id
        );

        $request = $this->composeRequest(array(
            'method' => 'cancelOrder',
            'params' => $params
        ));

        $this->container['user'] = $user;

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertNotEmpty($response);
        $this->assertObjectHasAttribute('result', $response);

        $pb = $response->result;
        $this->assertEquals($pb->status, \App\Model\ProductOrder::CANCELED);
    }

    public function testRejectOrder() {
        $this->prepareBooking();
        $user  = $this->createUser();
        $order = $this->createProductOrder($user->id, $this->product->id);

        $params = array(
            'product_order_id' => $order->id
        );

        $request = $this->composeRequest(array(
            'method' => 'rejectOrder',
            'params' => $params
        ));

        $this->container['user'] = $this->user;

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertNotEmpty($response);
        $this->assertObjectHasAttribute('result', $response);

        $pb = $response->result;
        $this->assertEquals($pb->status, \App\Model\ProductBooking::REJECTED);
    }

    public function testRejectBooking() {
        $this->prepareBooking();
        $user = $this->createUser();
        $productBooking = $this->createProductBooking($user->id, $this->booking->id);

        $params = array(
            'product_booking_id' => $productBooking->id
        );

        $request = $this->composeRequest(array(
            'method' => 'rejectBooking',
            'params' => $params
        ));

        $this->container['user'] = $this->user;

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertNotEmpty($response);
        $this->assertObjectHasAttribute('result', $response);

        $pb = $response->result;
        $this->assertEquals($pb->status, \App\Model\ProductBooking::REJECTED);
    }

    public function testTopBusinesses() {
        $this->prepareBooking();
        $this->createProductBooking($this->user->id, $this->booking->id);

        $request = $this->composeRequest(array(
            'method' => 'topBusinesses'
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);
        $this->assertNotEmpty($response);
        $this->assertObjectHasAttribute('result', $response);
        $this->assertCount(1, $response->result);
    }

    public function testProductStatus() {
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);
        $product  = $this->createProduct($business->id);
        $booking  = $this->createBooking($product->id);

        $this->createProductBooking($user->id, $booking->id);
        $this->createProductBooking($user->id, $booking->id);
        $this->createProductBooking($user->id, $booking->id);

        $params = array(
            'product_id' => $product->id
        );

        $request = $this->composeRequest(array(
            'method' => 'productStatus',
            'params' => $params
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);
        $this->assertNotEmpty($response);
        $this->assertObjectHasAttribute('result', $response);

        $status = $response->result;

        $this->assertNotEmpty($status);
        $this->assertInternalType('array', $status);
        $this->assertCount(3, $status);
    }

    public function testComposeRequestHelper() {
        $request = $this->composeRequest();
        $this->assertNotEmpty($request);
        $this->assertInstanceOf('\Junior\Serverside\Request', $request);
        $this->assertTrue($request->checkValid());

        $request = $this->composeRequest(array('jsonrpc' => '3.0'));
        $this->assertNotEmpty($request);
        $this->assertInstanceOf('\Junior\Serverside\Request', $request);
        $this->assertFalse($request->checkValid());

        $request = $this->composeRequest(array(
            'method' => 'customMethod',
            'params' => array(
                'first'  => 'first param',
                'second' => false
            ),
            'id' => 42
        ));
        $this->assertNotEmpty($request);
        $this->assertInstanceOf('\Junior\Serverside\Request', $request);
        $this->assertTrue($request->checkValid());

        $this->assertEquals($request->method, 'customMethod');
        $this->assertEquals($request->params->first, 'first param');
        $this->assertEquals($request->params->second, false);
        $this->assertEquals($request->id, 42);
    }

    /**
     * Creates json request
     *
     * @param array $fields rpc request fields
     * @return \Junior\Serverside\Request request object
     */
    protected function composeRequest($fields = array()) {
        $request = array_merge($this->request, $fields);
        return $this->server->makeRequest(json_encode($request));
    }

    private function prepareBooking() {
        $this->user     = $this->createUser();
        $this->business = $this->createBusiness($this->user->id);
        $this->product  = $this->createProduct($this->business->id);
        $this->booking  = $this->createBooking($this->product->id);
    }
}
