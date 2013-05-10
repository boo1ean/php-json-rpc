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

    public static function topByProductBookings() {
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

            RIGHT JOIN businesses biz
            ON biz.id = p.business_id


            GROUP BY biz.id
            ORDER BY bookings_count DESC
            LIMIT 10
        ";

        return $conn->query($sql)->fetchAll();
    }

    public static function checkForUpdates($business_id, $time) {
        $conn = self::connection();
        $sql = "
            SELECT

            count(*) as updates_count

            FROM bookings b

            RIGHT JOIN products p
            ON p.id = b.product_id AND p.business_id = ?

            INNER JOIN businesses biz
            ON biz.id = p.business_id

            WHERE b.updated_at >= ?
            OR p.updated_at >= ?
            OR biz.updated_at >= ?
        ";

        $params = array($business_id, $time, $time, $time);
        return $conn->query($sql, $params)->fetch();
    }
}
