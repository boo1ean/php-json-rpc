<?php
namespace App\Service;

use App\Ext\Service;
use Respect\Validation\Validator as v;
use App\Model\Product as Model;

class Product extends Service
{
    const MIN_RPP = 4;
    const MAX_RPP = 31;

    public function validation() {
        return array(
            'getProducts' => array(
                'business_id' => v::notEmpty()->int()->positive(),
                'rpp'         => v::int()->between(self::MIN_RPP, self::MAX_RPP),
                'page'        => v::int()->positive()
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
        $options = $this->pagination($p);
        $options['conditions'] = array('business_id = ?', $p['business_id']);
        return Model::find('all', $options);
    }
}
