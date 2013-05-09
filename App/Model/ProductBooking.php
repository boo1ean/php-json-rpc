<?php
namespace App\Model;

class ProductBooking extends \ActiveRecord\Model
{
    const PENDING  = 'pending';
    const APPROVED = 'aproved';
    const REJECTED = 'rejected';
    const CANCELED = 'canceled';

    public static $belongs_to = array(
        array('booking'),
        array('user')
    );
}
