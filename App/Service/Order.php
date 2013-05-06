<?php
namespace App\Service;

use App\Ext\Service;
use Respect\Validation\Validator as v;
use App\Model\User as UserModel;
use App\Model\ProductOrder as ProductOrderModel;

class Order extends Service
{
    public function validation() {
        return array(
            'requestOrder' => array(
                'user_id'    => v::notEmpty()->int()->positive(),
                'product_id' => v::notEmpty()->int()->positive()
            ),

            'setOrderStatus' => array(
                'user_id'          => v::notEmpty()->int()->positive(),
                'product_order_id' => v::notEmpty()->int()->positive(),
                'status'           => v::notEmpty()->string()->in(array(
                    ProductOrderModel::APPROVED,
                    ProductOrderModel::REJECTED,
                    ProductOrderModel::PENDING
                ))
            )
        );
    }

    /**
     * Requests product order from user
     *
     * @param integer $user_id
     * @param integer $product_id
     */
    public function _requestOrder($p) {
        try {
            ProductModel::find($p['product_id'], array('conditions' => array('status = ?', ProductModel::AVAILABLE)));
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Available Product with id {$p['product_id']} is not found.");
        }

        try {
            UserModel::find($p['user_id']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("User with id {$p['user_id']} doesn't exist.");
        }

        return ProductOrderModel::create($p);
    }

    public function _setOrderStatus() {
        // todo
    }
}
