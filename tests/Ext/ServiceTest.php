<?php

class ServiceTest extends TestCase
{
    public function testBaseServiceValidationRules() {
        $service = new App\Ext\Service(null);
        $validation = $service->validation();

        $this->assertInternalType('array', $validation);
        $this->assertEmpty($validation);
    }
}
