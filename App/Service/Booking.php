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
        $format = $this->container['config']['date_format'];
        $from   = date_create()->format($format);
        $to     = date_create()->add(new \DateInterval('P5Y'))->format($format);
        return array(
            'requestBooking' => array(
                'user_id'    => v::notEmpty()->int()->positive(),
                'booking_id' => v::notEmpty()->int()->positive(),
                'start_time' => v::notEmpty()->between($from, $to)
            ),

            'setBookingStatus' => array(
                'user_id'            => v::notEmpty()->int()->positive(),
                'product_booking_id' => v::notEmpty()->int()->positive(),
                'status'             => v::notEmpty()->string()->in(array(
                    ProductBookingModel::APPROVED,
                    ProductBookingModel::REJECTED,
                    ProductBookingModel::CANCELED,
                    ProductBookingModel::PENDING
                ))
            )
        );
    }

    /**
     * Add resquest for booking for specified product
     *
     * @param integer $user_id
     * @param integer $booking_id
     * @param string  $start_time W3C format
     */
    protected function _requestBooking($p) {
        try {
            $booking = BookingModel::find($p['booking_id']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Booking with id {$p['booking_id']} doesn't exist.");
        }

        try {
            UserModel::find($p['user_id']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("User with id {$p['user_id']} doesn't exist.");
        }

        $p['product_id'] = $booking->product->id;
        if (!$this->container['product-service']->isProductAvailable($p)) {
            throw new \Exception('Product is not available for booking during this period.');
        }
        unset($p['product_id']);

        return ProductBookingModel::create($p);
    }

    /**
     * Get list of pending booking requests for user
     *
     * @param $user_id
     */
    protected function _pendingBookings($p) {
        try {
            $user = UserModel::find($p['user_id']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("User with id {$p['user_id']} doesn't exist.");
        }

        return $user->getPendingBookings();
    }

    /**
     * @param integer $user_id business owner id
     * @param integer $product_booking_id
     * @param string  $status product booking status
     */
    protected function _setBookingStatus($p) {
        try {
            $user = UserModel::find($p['user_id']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("User with id {$p['user_id']} doesn't exist.");
        }

        try {
            $productBooking = ProductBookingModel::find($p['product_booking_id']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Product booking with id {$p['product_booking_id']} doesn't exist.");
        }

        if (ProductBookingModel::CANCELED === $p['status']) {
            if ($user->id != $productBooking->user_id) {
                throw new \Exception("Booking can be canceled only by its submitter.");
            }
        } else if (!$user->isAbleToUpdate($productBooking)) {
            throw new \Exception("User with id {$p['user_id']} doesn't have enough permissions.");
        }

        $productBooking->status = $p['status'];
        $productBooking->save();

        return $productBooking;
    }
}
