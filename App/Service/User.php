<?php
namespace App\Service;

use App\Ext\Service;
class User extends Service
{
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

        if (!$result->isValid()) {
            return $result->getMessages();
        } else {
            return true;
        }
    }
}
