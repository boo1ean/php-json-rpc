<?php
namespace App\Service;

use Zend\Authentication\AuthenticationService;
use App\Ext\Zend\Auth\Adapter as AuthAdapter;
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
    public function login($identity, $password) {
        $auth    = $this->container['auth-service'];
        $adapter = $auth->getAdapter();
        $adapter->setCredentials($identity, $password);
        $result  = $auth->authenticate();

        if (!$result->isValid()) {
            return $result->getMessages();
        } else {
            return 'Successfully logged in as ' . $identity;
        }
    }
}
