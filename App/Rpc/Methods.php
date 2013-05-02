<?php
namespace App\Rpc;
class Methods
{
    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function login($params) {
        $result = $this->container['user-service']->login($params);
        if (!$result) {
            throw new \Exception('Invalid email or password.');
        }

        return $result;
    }

    public function test() {
        $user = $this->container['user']; 

        if (is_null($user)) {
            throw new \Exception('Unauthorized');
        }

        return $user;
    }

    public function logout() {
        $this->container['auth-service']->clearIdentity();
        return true;
    }
}
