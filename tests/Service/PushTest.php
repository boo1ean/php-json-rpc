<?php
class PushTest extends TestCase
{
    protected $p, $user;

    public function setUp() {
        parent::setUp();
        $this->user = $this->createUser();
        $this->p = array(
            'user_id' => $this->user->id,
            'message' => 'Hello, World!'
        );
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidUserId() {
        $p = $this->p;
        $p['user_id'] = 1312312;
        $this->container['push-service']->notify($p);
    }

    /**
     * @expectedException Exception
     */
    public function testNoMessage() {
        $p = $this->p;
        unset($p['message']);
        $this->container['push-service']->notify($p);
    }

    public function testNotify() {
        $this->createDevice($this->user->id, array(
            'type' => App\Model\Device::ANDROID
        ));

        $this->createDevice($this->user->id, array(
            'type' => App\Model\Device::IOS
        ));

        // Create a stub for the SomeClass class.
        $apple = $this->getMockBuilder('App\\Ext\\ApplePusher')
                      ->disableOriginalConstructor()
                      ->getMock();

        $android = $this->getMockBuilder('App\\Ext\\AndroidPusher')
                        ->disableOriginalConstructor()
                        ->getMock();

        $apple->expects($this->once())
              ->method('push');

        $android->expects($this->once())
                ->method('push');

        $this->container['apple-pusher']   = $apple;
        $this->container['android-pusher'] = $android;

        $this->container['push-service']->notify($this->p);
    }
}
