<?php

use App\Model\User;
class MethodsTest extends TestCase
{
    protected $server;
    protected $request;

    public function setUp() {
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
        $result = json_decode($response->result);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
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
        $result = json_decode($response->result);
        $this->assertCount($count, $result);
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
