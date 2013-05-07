<?php

namespace Model;
class UserTest extends \TestCase
{
    public function testIsNotAbleToChangeStuff() {
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);
        $product  = $this->createProduct($business->id);
        $booking  = $this->createBooking($product->id);
        $pBooking = $this->createProductBooking($user->id, $booking->id);

        $this->assertNotEmpty($pBooking);

        $badGuy = $this->createUser();

        $this->assertFalse($badGuy->isAbleToUpdate($pBooking));
        $this->assertTrue($user->isAbleToUpdate($pBooking));
        $this->assertFalse($user->isAbleToUpdate(new \stdClass));
    }

    public function testIsAbleToUpdateProductOrder() {
        $user     = $this->createUser();
        $business = $this->createBusiness($user->id);
        $product  = $this->createProduct($business->id);

        $customer = $this->createUser();
        $order    = $this->createProductOrder($customer->id, $product->id);

        $this->assertTrue($user->isAbleToUpdate($order));
        $this->assertFalse($customer->isAbleToUpdate($order));
    }
}
