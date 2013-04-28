<?php
namespace App\Ext;

/**
 * Base service class
 */
class Service
{
    protected $context;

    /**
     * Initialize service dependencies
     *
     * @param mixed $context application context container
     * @return void
     */
    public function __construct($context) {
        $this->context = $context;
    }
}
