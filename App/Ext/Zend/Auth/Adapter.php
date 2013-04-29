<?php

namespace App\Ext\Zend\Auth;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class Adapter implements AdapterInterface
{
    protected $email;
    protected $password;

    /**
     * Sets email and password for authentication
     *
     * @param string $email
     * @param string $password
     * @return void
     */
    public function __construct($email = '', $password = '') {
        $this->setCredentials($email, $password);
    }

    /**
     * Set auth credentials
     *
     * @param string $email
     * @param string $password
     * @return void
     */
    public function setCredentials($email, $password) {
        $this->email    = $email;
        $this->password = $password;
    }

    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface
     *               If authentication cannot be performed
     */
    public function authenticate() {
        return new Result(Result::SUCCESS, $this->email);
    }
}
