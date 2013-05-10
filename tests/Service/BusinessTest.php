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

    public function testNoBusinesses() {
        $businesses = $this->container['business-service']->getBusinesses($this->p);
        $this->assertInternalType('array', $businesses);
        $this->assertEmpty($businesses);
    }

    public function testPagination() {
        $p = $this->p;
        $user = $this->createUser();
        $this->createBusinesses($user->id, 30);

        $businesses = $this->container['business-service']->getBusinesses($p);
        $this->assertCount(20, $businesses);

        $p['page'] = 2;
        $businesses = $this->container['business-service']->getBusinesses($p);
        $this->assertCount(10, $businesses);

        $p['rpp'] = 15;
        $businesses = $this->container['business-service']->getBusinesses($p);
        $this->assertCount(15, $businesses);

        $p['page'] = 3;
        $businesses = $this->container['business-service']->getBusinesses($p);
        $this->assertEmpty($businesses);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidRppMaxRange() {
        $p = $this->p;
        $p['rpp'] = 21;
        $this->container['business-service']->getBusinesses($p);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidRppMinRange() {
        $p = $this->p;
        $p['rpp'] = 4;
        $this->container['business-service']->getBusinesses($p);
    }

    public function testIncludeReviews() {
        $user = $this->createUser();
        $business = $this->createBusiness($user->id);
        $this->createReviews($user->id, $business->id, 10);

        $p = $this->p;
        $p['include_reviews'] = true;

        $businesses = $this->container['business-service']->getBusinesses($p);
        $this->assertNotEmpty($businesses);

        $business = array_shift($businesses);
        $reviews  = $business->reviews;
        $this->assertCount(10, $reviews);
    }

    public function testCheckForUpdates() {
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);

        $time = new DateTime('NOW');
        $p = array(
            'time'        => $time->format($this->container['config']['date_format']),
            'business_id' => $business->id
        );

        $result = $this->container['business-service']->checkForUpdates($p);
        $this->assertEquals(0, $result['updates_count']);

        $this->createProducts($business->id, 2);
        $result = $this->container['business-service']->checkForUpdates($p);
        $this->assertEquals(2, $result['updates_count']);

        $time->modify('+1 second');
        $p['time'] = $time->format($this->container['config']['date_format']);
        $result = $this->container['business-service']->checkForUpdates($p);
        $this->assertEquals(0, $result['updates_count']);
    }

}
