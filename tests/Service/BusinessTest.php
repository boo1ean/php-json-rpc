<?php
class BusinessTest extends TestCase
{
    public function testGetBusinesses() {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        $count = 10;
        $this->createBusinesses($user1->id, $count);
        $this->createBusinesses($user2->id, $count);

        $businesses = $this->container['business-service']->getBusinesses();
        $this->assertCount($count * 2, $businesses);

        $this->createBusiness($user1->id);

        $businesses = $this->container['business-service']->getBusinesses();
        $this->assertCount($count * 2 + 1, $businesses);
    }

    public function testNoBusinesses() {
        $businesses = $this->container['business-service']->getBusinesses();
        $this->assertInternalType('array', $businesses);
        $this->assertEmpty($businesses);
    }
}
