<?php
namespace App\Model;
class Product extends \ActiveRecord\Model
{
    static $belongs_to = array(
        array('business', 'readonly' => true)
    );

    public static $has_many = array(
        array('bookings')
    );

    public static $validates_presence_of = array(
        array('name')
   );
}
