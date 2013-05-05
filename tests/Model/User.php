<?php
class UserTest extends TestCase
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
    }
}
