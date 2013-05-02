<?php
namespace App\Model;
class Business extends \ActiveRecord\Model
{
    static $belongs_to = array(
        array('user', 'readonly' => true)
    );

    public static $validates_presence_of = array(
        array('name')
   );
}
