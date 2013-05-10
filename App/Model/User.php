<?php
namespace App\Model;
class User extends \ActiveRecord\Model
{
    public static $validates_presence_of = array(
        array('email'),
        array('password')
    );

    public static $has_many = array(
        array('businesses'),
        array('devices'),
        array('product_bookings')
    );

    /**
     * Data encryption algorithm
     *
     * @param string $data
     * @return string encrypted data
     */
    public static function encrypt($text) {
        return md5($text);
    }

    /**
     * Set password attribute auto-encryption
     *
     * @param string $plain
     * @return void
     */
    public function set_password($plain) {
        $this->assign_attribute('password', static::encrypt($plain));
    }

    /**
     * Get list of pending bookings requests
     *
     * @return array pending bookings report
     */
    public function getPendingBookings() {
        $conn = self::connection();
        $sql = "
            SELECT 

            biz.name as business_name,
            biz.id as business_id,
            p.name as product_name,
            p.id as product_id,
            b.duration,
            b.price,
            pb.id as product_booking_id,
            pb.created_at

            FROM product_bookings pb

            INNER JOIN bookings b
            ON b.id = pb.booking_id AND pb.status = ? AND pb.start_time > NOW()

            INNER JOIN products p
            ON p.id = b.product_id

            INNER JOIN businesses biz
            ON biz.id = p.business_id

            INNER JOIN users u
            ON u.id = biz.user_id AND biz.user_id = ?
        ";

        $params = array(ProductBooking::PENDING, $this->id);
        return $conn->query($sql, $params)->fetchAll();
    }

    public function getPendingOrders() {
        $conn = self::connection();
        $sql = "
            SELECT 

            biz.name as business_name,
            biz.id as business_id,
            p.name as product_name,
            p.id as product_id,
            p.price,
            po.id as product_order_id,
            po.created_at

            FROM product_orders po

            INNER JOIN products p
            ON p.id = po.product_id AND po.status = ?

            INNER JOIN businesses biz
            ON biz.id = p.business_id

            INNER JOIN users u
            ON u.id = biz.user_id AND biz.user_id = ?
        ";

        $params = array(ProductOrder::PENDING, $this->id);
        return $conn->query($sql, $params)->fetchAll();
    }

    /**
     * Check if user able to update specified entity
     *
     * @param object $entity
     * @return bool
     */
    public function isAbleToUpdate($entity) {
        switch (true) {
            case ($entity instanceof \App\Model\ProductBooking):
                return $this->isAbleToUpdateProductBooking($entity);
            case ($entity instanceof \App\Model\ProductOrder):
                return $this->isAbleToUpdateProductOrder($entity);

            default:
                return false;
        }
    }

    /**
     * Checks if user is able to update product booking
     *
     * @param App\Model\ProductBooking
     * @return bool
     */
    protected function isAbleToUpdateProductBooking($productBooking) {
        $owner = $productBooking->booking->product->business->user;
        return $owner->id == $this->id;
    }

    /**
     * Checks if user is able to update product order
     *
     * @param App\Model\ProductOrder
     * @return bool
     */
    protected function isAbleToUpdateProductOrder($productOrder) {
        $owner = $productOrder->product->business->user;
        return $owner->id == $this->id;
    }
}
