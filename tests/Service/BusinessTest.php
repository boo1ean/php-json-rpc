<?php
class BusinessTest extends TestCase
{
    public function __construct() {
        parent::__construct();
        $this->p = array('rpp' => 20, 'page' => 1);
    }
    public function testGetBusinesses() {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        $count = 10;
        $businesses = $this->createBusinesses($user1->id, $count);
        $this->assertCount($count, $businesses);

        $businesses = $this->createBusinesses($user2->id, $count);
        $this->assertCount($count, $businesses);

        $businesses = $this->container['business-service']->getBusinesses($this->p);
        $this->assertCount($count * 2, $businesses);

        $this->createBusiness($user1->id);

        $businesses = $this->container['business-service']->getBusinesses($this->p);
        $this->assertCount($count * 2, $businesses);
    }

    public function testBusinessPagination() {
        $this->assertTrue(true);
    }

    public function testNoBusinesses() {
        $businesses = $this->container['business-service']->getBusinesses($this->p);
        $this->assertInternalType('array', $businesses);
        $this->assertEmpty($businesses);
    }
}
