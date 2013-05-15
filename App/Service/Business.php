<?php
namespace App\Service;

use App\Ext\Service;
use Respect\Validation\Validator as v;
use App\Model\Business as BusinessModel;

class Business extends Service
{
    const MAX_RPP = 21;
    const MIN_RPP = 4;

    public function validation() {
        return array(
            'getBusinesses' => array(
                'rpp'  => v::int()->between(self::MIN_RPP, self::MAX_RPP),
                'page' => v::int()->positive()
            ),

            'checkForUpdates' => array(
                'business_id' => v::notEmpty()->int()->positive(),
                'time'        => v::notEmpty()
            )
        );
    }

    /**
     * Get list of businesses
     *
     * @param integer $rpp number of records per page
     * @param integer $page page number
     * @return array collection of businesses
     */
    protected function _getBusinesses($p) {
        $options = $this->pagination($p);

        if (isset($p['include_reviews']) && $p['include_reviews']) {
            $options['include'] = array('reviews');
        }

        $businesses = BusinessModel::find('all', $options);
        return $businesses;
    }

    protected function _topBusinesses() {
        return BusinessModel::topByProductBookings();
    }

    /**
     * @param integer $business_id
     * @param string  $time W3C format
     */
    protected function _checkForUpdates($p) {
        return BusinessModel::checkForUpdates($p['business_id'], $p['time']);
    }
}
