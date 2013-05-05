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
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);
        $product  = $this->createProduct($business->id);
        $booking  = $this->createBooking($product->id);

        $time   = new \DateTime('NOW');
        $params = array(
            'booking_id' => $booking->id,
            'start_time' => $time->format(\DateTime::ISO8601)
        );

        $request = $this->composeRequest(array(
            'method' => 'book',
            'params' => $params
        ));

        // Test unauthorized
        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('error', $response);
        $this->container['user'] = $user;

        $request = $this->composeRequest(array(
            'method' => 'book',
            'params' => $params
        ));

        $response = $this->server->handleRequest($request);
        $response = json_decode($response);

        $this->assertObjectHasAttribute('result', $response);
        $productBooking = $response->result;

        $this->assertEquals($productBooking->user_id, $user->id);
        $this->assertEquals($productBooking->booking_id, $booking->id);
    }

    public function testRpcInterface() {
        $method = $this->container['rpc-methods'];

        $this->assertTrue(is_callable(array($method, 'login')));
        $this->assertTrue(is_callable(array($method, 'logout')));
        $this->assertTrue(is_callable(array($method, 'products')));
        $this->assertTrue(is_callable(array($method, 'businesses')));
        $this->assertTrue(is_callable(array($method, 'addReview')));
        $this->assertTrue(is_callable(array($method, 'book')));
        $this->assertTrue(is_callable(array($method, 'productStatus')));

        $this->assertFalse(is_callable(array($method, 'prepare')));
        $this->assertFalse(is_callable(array($method, 'checkSession')));
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
}
