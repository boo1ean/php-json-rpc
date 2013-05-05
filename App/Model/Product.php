<?php
namespace App\Model;
class Product extends \ActiveRecord\Model
{
    static $belongs_to = array(
        array('business')
    );

    public static $has_many = array(
        array('bookings')
    );

    public static $validates_presence_of = array(
        array('name')
   );

    /**
     * Get product booking status
     */
    public function statusReport() {
        $conn = self::connection();
        $sql = "
            SELECT p.name, b.duration, pb.start_time FROM product_bookings pb

            INNER JOIN bookings b
            ON b.id = pb.booking_id AND b.product_id = ?
            AND pb.status <> ?
            AND pb.start_time > NOW()

            INNER JOIN products p
            ON p.id = b.product_id
        ";

        $params = array($this->id, ProductBooking::REJECTED);
        return $conn->query($sql, $params)->fetchAll();
    }
}
