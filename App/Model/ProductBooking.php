<?php
namespace App\Model;

class ProductBooking extends \ActiveRecord\Model
{
    const PENDING  = 'pending';
    const APPROVED = 'aproved';
    const REJECTED = 'rejected';

    static $belongs_to = array(
        array('booking'),
        array('user')
    );
}
