<?php
namespace App\Service;

use App\Ext\Service;
use Respect\Validation\Validator as v;
use App\Model\Business as Model;

class Business extends Service
{
    const MAX_RPP = 21;
    const MIN_RPP = 4;

    public function validation() {
        return array(
            'getBusinesses' => array(
                'rpp'  => v::int()->between(self::MIN_RPP, self::MAX_RPP),
                'page' => v::int()->positive()
            )
        );
    }

    protected function _getBusinesses($p) {
        $options    = $this->pagination($p);
        $businesses = Model::find('all', $options);
        return $businesses;
    }
}
