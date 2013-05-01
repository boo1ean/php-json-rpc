<?php
namespace App\Model;
class Product extends \ActiveRecord\Model
{
    static $belongs_to = array(
        array('store', 'readonly' => true)
    );

    public static $validates_presence_of = array(
        array('name')
   );
}
