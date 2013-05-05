<?php
namespace App\Model;
class Business extends \ActiveRecord\Model
{
    public static $belongs_to = array(
        array('user')
    );

    public static $has_many = array(
        array('reviews')
    );

    public static $validates_presence_of = array(
        array('name')
    );

    public static function topByProductBookings($count = 10) {
        $conn = self::connection();
        $sql = "
            SELECT

            biz.*,
            COUNT(pb.id) AS bookings_count

            FROM product_bookings pb

            INNER JOIN bookings b
            ON b.id = pb.booking_id

            INNER JOIN products p
            ON p.id = b.product_id

            INNER JOIN businesses biz
            ON biz.id = p.business_id

            GROUP BY biz.id
            ORDER BY bookings_count DESC;
        ";

        return $conn->query($sql)->fetchAll();
    }
}
