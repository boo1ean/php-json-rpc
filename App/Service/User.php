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
        $auth        = new AuthenticationService();
        $authAdapter = new AuthAdapter($identity, $password);
        $result      = $auth->authenticate($authAdapter);

        if (!$result->isValid()) {
            // Authentication failed; print the reasons why
            foreach ($result->getMessages() as $message) {

            }
        } else {

        }
    }
}
