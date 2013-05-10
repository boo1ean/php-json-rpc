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

    public function testAddDevice() {
        $user = $this->createUser();
        $type = App\Model\Device::IOS;
        $p = array(
            'user_id' => $user->id,
            'type'    => $type,
            'token'   => $this->faker->md5
        );

        $device = $this->container['user-service']->addDevice($p);

        $this->assertNotEmpty($device);
        $this->assertEquals($type, $device->type);
        $this->assertEquals($user->id, $device->user_id);
    }

    /**
     * @expectedException Exception
     */
    public function testAddDeviceInvalidUserId() {
        $type = App\Model\Device::IOS;
        $p = array(
            'user_id' => 131231,
            'type'    => $type,
            'token'   => $this->faker->md5
        );

        $device = $this->container['user-service']->addDevice($p);
    }

    /**
     * @expectedException Exception
     */
    public function testAddDeviceInvalidType() {
        $user = $this->createUser();

        $p = array(
            'user_id' => $user->id,
            'type'    => 'qwerty',
            'token'   => $this->faker->md5
        );

        $device = $this->container['user-service']->addDevice($p);
    }

    /**
     * @expectedException Exception
     */
    public function testAddDeviceNoToken() {
        $user = $this->createUser();

        $p = array(
            'user_id' => $user->id,
            'type'    => App\Model\Device::IOS
        );

        $device = $this->container['user-service']->addDevice($p);
    }
}
