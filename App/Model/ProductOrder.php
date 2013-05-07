<?php
namespace App\Model;

class ProductOrder extends \ActiveRecord\Model
{
    const PENDING  = 'pending';
    const APPROVED = 'aproved';
    const REJECTED = 'rejected';

    public static $belongs_to = array(
        array('product'),
        array('user')
    );
}
