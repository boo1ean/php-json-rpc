<?php
namespace App\Model;
class Device extends \ActiveRecord\Model
{
    const IOS     = 'ios';
    const ANDROID = 'android';
    const WP8     = 'wp8';

    static $belongs_to = array(
        array('user')
    );
}
