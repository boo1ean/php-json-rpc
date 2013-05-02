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
}
