<?php

use App\Model\User;
class UserTest extends TestCase
{
    public function testLogin() {
        $params = array(
            'email'    => 'email@example.com',
            'password' => 'qwerty'
        );

        $user = User::create($params);
        $this->assertNotEmpty($user);

        $result = $this->container['user-service']->login($params['email'], $params['password']);
        $this->assertTrue($result);
    }
}
