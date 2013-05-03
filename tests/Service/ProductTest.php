<?php
class ProductService extends TestCase
{
    public function testGetProducts() {
        $count    = 10;
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);
        $products = $this->createProducts($business->id, $count);

        $p = array('business_id' => $business->id);
        $result = $this->container['product-service']->getProducts($p);
        $this->assertCount($count, $result);
    }

    public function testNoProducts() {
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);
        $p = array('business_id' => $business->id);

        $result = $this->container['product-service']->getProducts($p);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

     /**
      * @expectedException Exception
      */
    public function testInvalidBusinessId() {
        $p = array('business_id' => 'custom-string');
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
}
