<?php
namespace App\Service;

use App\Ext\Service;
use Respect\Validation\Validator as v;
class Product extends Service
{
    public function validation() {
        return array(
            'getProducts' => array(
                'business_id' => v::notEmpty()->numeric()->positive()
            )
        );
    }

    /**
     * Get list of product for specific business'
     *
     * @param integer $business_id
     * @return array collection of product
     */
    protected function _getProducts($p) {
        $products = Product::find('all', array('conditions' => 'business_id = ?', $p['business_id']));
        return $products;
    }
}
