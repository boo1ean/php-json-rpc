<?php
require_once APP_PATH . '/Ext/Service.php';

class CustomService extends App\Ext\Service
{
    const RESULT = 'result';

    protected function _usefulMethod($p = null) {
        if (!$p) {
            throw new Exception('Error!');
        }

        return self::RESULT;
    }
}

class ServiceTest extends TestCase
{
    public function testBaseServiceValidationRules() {
        $service = new App\Ext\Service($this->container);
        $validation = $service->validation();

        $this->assertInternalType('array', $validation);
        $this->assertEmpty($validation);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Done!
     */
    public function testEventSuccess() {
        $service = new CustomService($this->container);
        $that = $this;
        $params = array(50, 100);
        $this->container['vent']->on('CustomService.usefulMethod.success', function($c, $p, $result) use ($that, $params) {
            $that->assertEquals($params, $p);
            $that->assertEquals(CustomService::RESULT, $result);
            throw new Exception('Done!');
        });

        $service->usefulMethod($params);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Done!
     */
    public function testEventError() {
        $service = new CustomService($this->container);
        $that = $this;
        $this->container['vent']->on('CustomService.usefulMethod.error', function($c, $p, $e) use ($that) {
            $that->assertInstanceOf('Exception', $e);
            $that->assertEmpty($p);
            throw new Exception('Done!');
        });

        $service->usefulMethod(array());
    }
}
