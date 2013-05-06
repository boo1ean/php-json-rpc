<?php
namespace App\Model;

class ProductOrder extends ProductBooking
{
    public static $belongs_to = array(
        array('product'),
        array('user')
    );
}
