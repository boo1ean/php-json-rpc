<?php
class ProductService extends TestCase
{
    public function testGetProducts() {
        $count    = 10;
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);
        $products = $this->createProducts($business->id, $count);

        $this->assertCount($count, $products);
    }
}
