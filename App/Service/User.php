<?php
namespace App\Service;

use App\Ext\Service;
use App\Model\User as UserModel;
use App\Model\Device as DeviceModel;
use Respect\Validation\Validator as v;

class User extends Service
{
    public function validation() {
        return array(
            'login' => array(
                'email'    => v::notEmpty()->email(),
                'password' => v::notEmpty()->string()
            ),

            'addDevice' => array(
                'user_id' => v::notEmpty()->int()->positive(),
                'token'   => v::notEmpty()->string(),
                'type'    => v::notEmpty()->string()->in(array(
                    DeviceModel::IOS,
                    DeviceModel::ANDROID,
                    DeviceModel::WP8
                ))
            )
        );
    }

    /**
     * Login and create use session
     *
     * @param string $identity currently email
     * @param string $password
     * @return void 
     */
    protected function _login($p) {
        $auth    = $this->container['auth-service'];
        $adapter = $auth->getAdapter();
        $adapter->setCredentials($p['email'], $p['password']);
        $result  = $auth->authenticate();

        return $result->isValid();
    }

    protected function _logout($p) {
        return $this->container['auth-service']->clearIdentity();
    }

    /**
     * @param integer $user_id
     * @param string  $device_type
     * @param string  $token
     */
    protected function _addDevice($p) {
        try {
            $user = UserModel::find($p['user_id']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("User with id {$p['user_id']} doesn't exist.");
        }

        return $user->create_device($p);
    }
}
