<?php
namespace App\Model;

class ProductOrder extends \ActiveRecord\Model
{
    const PENDING  = 'pending';
    const APPROVED = 'aproved';
    const REJECTED = 'rejected';
    const CANCELED = 'canceled';

    public static $belongs_to = array(
        array('product'),
        array('user')
    );
}
