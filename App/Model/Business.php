<?php
namespace App\Model;
class Business extends \ActiveRecord\Model
{
    public static $belongs_to = array(
        array('user')
    );

    public static $has_many = array(
        array('reviews')
    );

    public static $validates_presence_of = array(
        array('name')
   );
}
