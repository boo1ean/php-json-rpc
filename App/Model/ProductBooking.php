<?php
namespace App\Model;

class ProductBooking extends \ActiveRecord\Model
{
    const PENDING  = 'pending';
    const APPROVED = 'aproved';
    const REJECTED = 'rejected';

    static $belongs_to = array(
        array('booking'),
        array('user')
    );

    /**
     * Set status to approved
     */
    public function approve() {
        $this->status = self::APPROVED;
        return $this->save();
    }

    /**
     * Set status to rejected
     */
    public function reject() {
        $this->status = self::REJECTED;
        return $this->save();
    }
}
