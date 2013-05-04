<?php
namespace App\Model;

class Review extends \ActiveRecord\Model
{
    static $belongs_to = array(
        array('business'),
        array('user')
    );
}
