<?php
class ReviewTest extends TestCase
{
    protected $p, $user, $business;

    public function setUp() {
        parent::setUp();
        $this->user     = $this->createUser();
        $this->business = $this->createBusiness($this->user->id);
        $this->p =  array(
            'user_id'     => $this->user->id,
            'business_id' => $this->business->id,
            'title'       => $this->faker->name,
            'body'        => $this->faker->text
        );
    }

    public function testValidAddReview() {
        $review = $this->container['review-service']->addReview($this->p);
        $this->assertNotEmpty($review);
    }

    public function testValidAddReviewCustom() {
        $p = $this->p;

        $p['title'] = 'custom-title';
        $p['body']  = 'custom-body';

        $review = $this->container['review-service']->addReview($p);
        $this->assertNotEmpty($review);

        $this->assertEquals($review->title, $p['title']);
        $this->assertEquals($review->body, $p['body']);
        $this->assertEquals($review->user_id, $p['user_id']);
        $this->assertEquals($review->business_id, $p['business_id']);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidBusinessId() {
        $p = $this->p;
        $p['business_id'] = $faker->randomFloat;
        $this->container['review-service']->addReview($p);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidUserId() {
        $p = $this->p;
        $p['user_id'] = $faker->randomFloat;
        $this->container['review-service']->addReview($p);
    }

    /**
     * @expectedException Exception
     */
    public function testNoTitle() {
        $p = $this->p;
        unset($p['title']);
        $this->container['review-service']->addReview($p);
    }

    /**
     * @expectedException Exception
     */
    public function testBusinessNotFound() {
        $p = $this->p;
        $p['business_id'] = 3948384;
        $this->container['review-service']->addReview($p);
    }

    /**
     * @expectedException Exception
     */
    public function testUserNotFound() {
        $p = $this->p;
        $p['user_id'] = 3948384;
        $this->container['review-service']->addReview($p);
    }

    /**
     * @expectedException Exception
     */
    public function testNoBody() {
        $p = $this->p;
        unset($p['body']);
        $this->container['review-service']->addReview($p);
    }

    /**
     * @expectedException Exception
     */
    public function testEmptyTitle() {
        $p = $this->p;
        $p['title'] = '';
        $this->container['review-service']->addReview($p);
    }

    /**
     * @expectedException Exception
     */
    public function testEmptyBody() {
        $p = $this->p;
        $p['body'] = '';
        $this->container['review-service']->addReview($p);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidTitleLength() {
        $p = $this->p;
        $p['title'] = str_repeat('x', 300);
        $this->container['review-service']->addReview($p);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidBodyLengthMax() {
        $p = $this->p;
        $p['body'] = str_repeat('x', 3000);
        $this->container['review-service']->addReview($p);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidBodyLengthMin() {
        $p = $this->p;
        $p['body'] = str_repeat('x', 3);
        $this->container['review-service']->addReview($p);
    }
}
