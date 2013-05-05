<?php

use App\Model\User;
class UserTest extends TestCase
{
    public function testLogin() {
        $params = array(
            'email'    => $this->faker->email,
            'password' => $this->faker->phoneNumber
        );

        $user = User::create($params);
        $this->assertNotEmpty($user);

        $result = $this->container['user-service']->login($params);
        $this->assertTrue($result);
    }

    public function testLogout() {
        $password = 'test';
        $user = $this->createUser(array(
            'password' => $password
        ));

        $result = $this->container['user-service']->login(array(
            'email'    => $user->email,
            'password' => $password
        ));

        $this->assertNotEmpty($result);
        $this->assertNotEmpty($this->container['auth-service']->getIdentity());

        $this->container['user-service']->logout();
        $this->assertNull($this->container['auth-service']->getIdentity());
    }
}
