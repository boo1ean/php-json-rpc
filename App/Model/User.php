<?php
namespace App\Model;
class User extends \ActiveRecord\Model
{
    public static $validates_presence_of = array(
        array('email'),
        array('password')
    );

    /**
     * Data encryption algorithm
     *
     * @param string $data
     * @return string encrypted data
     */
    public static function encrypt($text) {
        return md5($text);
    }

    /**
     * Set password attribute auto-encryption
     *
     * @param string $plain
     * @return void
     */
    public function set_password($plain) {
        $this->assign_attribute('password', static::encrypt($plain));
    }
}
