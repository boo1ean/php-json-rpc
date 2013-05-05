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
        $format = $this->container['config']['date_format'];
        $from   = date_create()->format($format);
        $to     = date_create()->add(new \DateInterval('P5Y'))->format($format);

        return array(
            'getProducts' => array(
                'business_id' => v::notEmpty()->int()->positive(),
                'rpp'         => v::int()->between(self::MIN_RPP, self::MAX_RPP),
                'page'        => v::int()->positive()
            ),

            'productStatus' => array(
                'product_id' => v::notEmpty()->int()->positive()
            ),

            'isProductAvailable' => array(
                'product_id' => v::notEmpty()->int()->positive(),
                'time'       => v::notEmpty()->date($format)->between($from, $to),
                'duration'   => v::notEmpty()->int()->positive()
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

        if (isset($p['include_bookings']) && $p['include_bookings']) {
            $options['include'] = array('bookings');
        }

        return Model::find('all', $options);
    }

    /**
     * Get product status/availability
     *
     * @param integer $product_id
     * @return array
     */
    protected function _productStatus($p) {
        try {
            $product = Model::find($p['product_id']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Product with id {$p['product_id']} doesn't exist");
        }

        return $product->statusReport();
    }

    /**
     * Check is product with specified id is available
     *
     * @param integer $product_id
     * @return bool availability status
     */
    protected function _isProductAvailable($p) {
        $report = $this->_productStatus($p);
        return $report;
    }
}
