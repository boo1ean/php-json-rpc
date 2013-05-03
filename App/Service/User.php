<?php
namespace App\Service;

use App\Ext\Service;
use Respect\Validation\Validator as v;
class User extends Service
{
    public function validation() {
        return array(
            'login' => array(
                'email'    => v::notEmpty()->email(),
                'password' => v::notEmpty()->string()
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
}
