<?php
namespace App\Service;

use App\Ext\Service;
use Respect\Validation\Validator as v;
use App\Model\Review as Model;
use App\Model\User as UserModel;
use App\Model\Business as BusinessModel;

class Review extends Service
{
    public function validation() {
        return array(
            'addReview' => array(
                'user_id'     => v::notEmpty()->int()->positive(),
                'business_id' => v::notEmpty()->int()->positive(),
                'title'       => v::notEmpty()->string()->length(0, 255),
                'body'        => v::notEmpty()->string()->length(0, 2000)
            )
        );
    }

    /**
     * Add review to business
     *
     * @param integer $user_id
     * @param integer $business_id
     * @param string  $title review title
     * @param string  $body review body
     */
    public function _addReview($p) {
        try {
            UserModel::find($p['user_id']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("User with id {$p['user_id']} doesn't exist.");
        }

        try {
            BusinessModel::find($p['business_id']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Business with id {$p['business_id']} doesn't exist.");
        }

        return Model::create($p);
    }
}
