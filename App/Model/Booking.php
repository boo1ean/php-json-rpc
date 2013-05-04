<?php
namespace App\Model;
class Booking extends \ActiveRecord\Model
{
    static $belongs_to = array(
        array('product')
    );
}
