<?php
namespace App\Service;

use App\Ext\Service;
use Respect\Validation\Validator as v;
use App\Model\User as UserModel;
use App\Model\Booking as BookingModel;
use App\Model\ProductBooking as ProductBookingModel;

class Booking extends Service
{
    public function validation() {
        return array(
            'requestBooking' => array(
                'user_id'    => v::notEmpty()->int()->positive(),
                'booking_id' => v::notEmpty()->int()->positive(),
                'start_time' => v::notEmpty()->date(\DateTime::ISO8601)
            )
        );
    }

    /**
     * Add resquest for booking for specified product
     *
     * @param integer $user_id
     * @param integer $booking_id
     * @param string  $start_time ISO8601 format
     */
    protected function _requestBooking($p) {
        // TODO Check if available

        try {
            $booking = BookingModel::find($p['booking_id']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Booking with id {$p['booking_id']} doesn't exist.");
        }

        try {
            $user = UserModel::find($p['user_id']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("User with id {$p['user_id']} doesn't exist.");
        }

        return ProductBookingModel::create($p);
    }
}
