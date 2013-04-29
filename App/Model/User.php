<?php
namespace App\Model;
class User extends \ActiveRecord\Model
{
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
