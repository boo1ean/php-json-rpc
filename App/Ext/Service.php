<?php
namespace App\Ext;

/**
 * Base service class
 */
class Service
{
    protected $container;

    /**
     * Initialize service dependencies
     *
     * @param mixed $container application DI container
     * @return void
     */
    public function __construct($container) {
        $this->container = $container;
    }

    /**
     * Method call decoration
     *
     * @param string $method name of called method
     * @param array $params method arguments
     * @return mixed result of meethod call
     */
    public function __call($method, $params) {
        $p = $params[0];
        return call_user_method('_' . $method, $this, $p);
    }
}
