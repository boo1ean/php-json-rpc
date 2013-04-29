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
}
