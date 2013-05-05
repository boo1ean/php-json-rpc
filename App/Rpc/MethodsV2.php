<?php
namespace App\Rpc;

// @codeCoverageIgnoreStart
class MethodsV2 extends Methods
{
    public function businesses($p = array()) {
        $businesses = parent::businesses($p);
        return array(
            'count' => count($businesses),
            'items' => $businesses
        );
    }

    public function products($p) {
        $products = parent::products($p);
        return array(
            'count' => count($products),
            'items' => $products
        );
    }
}
