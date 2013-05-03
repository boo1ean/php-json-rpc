<?php
namespace App\Service;

use App\Ext\Service;
use Respect\Validation\Validator as v;
use App\Model\Product as Model;

class Product extends Service
{
    public function validation() {
        return array(
            'getProducts' => array(
                'business_id' => v::notEmpty()->int()->positive()
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
        $products = Model::find('all', array(
            'conditions' => array(
                'business_id = ?', $p['business_id']
            )
        ));

        return $products;
    }
}
