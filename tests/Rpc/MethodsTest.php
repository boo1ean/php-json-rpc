<?php

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
            'id'      => uniqid()
        );

    }

    public function testLogin() {
        $request = $this->composeRequest(array(
            'method' => 'login',
            'params' => array(
                'email'    => 'address@example.com',
                'password' => 'custom-pwd'
            )
        ));

        $response = $this->server->handleRequest($request);
        $this->assertNotEmpty($response);

        $response = json_decode($response);
        $this->assertNotEmpty($response);
        $this->assertNotEmpty($response->result);
        $this->assertEquals($request->id, $response->id);
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
