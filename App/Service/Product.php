<?php
namespace App\Service;

use App\Ext\Service;
use Respect\Validation\Validator as v;
use App\Model\Product as ProductModel;
use App\Model\Booking as BookingModel;

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
                'business_id' => v::oneOf(v::int()->positive(), v::nullValue()),
                'rpp'         => v::int()->between(self::MIN_RPP, self::MAX_RPP),
                'page'        => v::int()->positive()
            ),

            'productStatus' => array(
                'product_id' => v::notEmpty()->int()->positive()
            ),

            'isProductAvailable' => array(
                'product_id' => v::notEmpty()->int()->positive(),
                'booking_id' => v::notEmpty()->int()->positive(),
                'start_time' => v::notEmpty()->between($from, $to)
            )
        );
    }

    /**
     * Get list of product for specific business
     *
     * @param integer $business_id
     * @return array collection of product
     */
    protected function _getProducts($p) {
        $options = $this->pagination($p);

        $options['order'] = 'name asc';

        if (!empty($p['business_id'])) {
            $options['conditions'] = array('business_id = ? AND status = ?', $p['business_id'], ProductModel::AVAILABLE);
        } else {
            $options['conditions'] = array('status = ?', ProductModel::AVAILABLE);
        }

        if (isset($p['include_bookings']) && $p['include_bookings']) {
            $options['include'] = array('bookings');
        }

        return ProductModel::find('all', $options);
    }

    /**
     * Get product status/availability
     *
     * @param integer $product_id
     * @return array
     */
    protected function _productStatus($p) {
        try {
            $product = ProductModel::find($p['product_id']);
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
        $options = array(
            'conditions' => array('id = ? AND product_id = ?', $p['booking_id'], $p['product_id'])
        );

        $booking = BookingModel::find('first', $options);
        if (is_null($booking)) {
            throw new \InvalidArgumentException("Booking with id {$p['booking_id']} for specified product doesn't exist");
        }

        $report = $this->_productStatus($p);
        $time = \DateTime::createFromFormat($this->container['config']['date_format'], $p['start_time']);
        $requestedTime = array(
            'start' => $time->getTimestamp(),
            'end'   => $time->add(new \DateInterval("PT{$booking->duration}M"))->getTimestamp()
        );

        $available = true;
        foreach ($report as $booking) {
            $bookedTime = array(
                'start' => date_create($booking['start_time'])->getTimestamp(),
                'end'   => date_create($booking['start_time'])->add(new \DateInterval("PT{$booking['duration']}M"))->getTimestamp()
            );

            if ($this->intersection($requestedTime, $bookedTime)) {
                $available = false;
                break;
            }
        }

        return $available;
    }

    /**
     * Check if two time ranges intersect
     *
     * @param array $first time range with start, end timestamps
     * @param array $second time range with start, end timestamps
     * @return bool
     */
    private function intersection($first, $second) {
        return $first['start'] >= $second['start'] && $first['start'] <= $second['end'] ||
               $first['end'] >= $second['start'] && $first['end'] <= $second['end'];
    }
}
