<?php
class ProductService extends TestCase
{
    public $p = array(
        'rpp'         => 30,
        'page'        => 1,
        'business_id' => 1
    );

    public function testGetProducts() {
        $count    = 10;
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);
        $products = $this->createProducts($business->id, $count);

        $p = array_merge($this->p, array('business_id' => $business->id));
        $result = $this->container['product-service']->getProducts($p);
        $this->assertCount($count, $result);
    }

    public function testNoProducts() {
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);
        $p = array_merge($this->p, array('business_id' => $business->id));

        $result = $this->container['product-service']->getProducts($p);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidBusinessId() {
        $p = array_merge($this->p, array('business_id' => 'custom'));
        $this->container['product-service']->getProducts($p);
    }

    /**
     * @expectedException Exception
     */
    public function testNoParams() {
        $this->container['product-service']->getProducts();
    }

    /**
     * @expectedException Exception
     */
    public function testEmptyBusinessId() {
        $p = array('business_id' => '');
        $this->container['product-service']->getProducts($p);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidPage() {
        $p = $this->p;
        $p['page'] = '';
        $this->container['product-service']->getProducts($p);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidMaxRpp() {
        $p = $this->p;
        $p['rpp'] = 31;
        $this->container['product-service']->getProducts($p);
    }
}
