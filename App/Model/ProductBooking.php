<?php
namespace App\Model;

class ProductBooking extends \ActiveRecord\Model
{
    static $belongs_to = array(
        array('product'),
        array('user')
    );
}
