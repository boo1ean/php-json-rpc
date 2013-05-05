<?php
namespace App\Model;
class Booking extends \ActiveRecord\Model
{
    public static $belongs_to = array(
        array('product')
    );

    public static $has_many = array(
        array('product_bookings')
    );
}
