<?php
namespace App\Service;

use App\Ext\Service;
use App\Model\Business as Model;
class Business extends Service
{
    protected function _getBusinesses() {
        $businesses = Model::find('all');
        return $businesses;
    }
}
