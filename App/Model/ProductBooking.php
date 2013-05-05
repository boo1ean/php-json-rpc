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
        return $this->setStatus(self::APPROVED);
    }

    /**
     * Set status to rejected
     */
    public function reject() {
        return $this->setStatus(self::REJECTED);
    }

    /**
     * Update status
     *
     * @param enum $status
     * @return $this
     */
    protected function setStatus($status) {
        $this->status = $status;
        if (!$this->save()) {
            throw new \Exception('Cann\'t update product booking status.');
        }

        return $this;
    }
}
