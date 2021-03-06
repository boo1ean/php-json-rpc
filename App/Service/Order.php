<?php
namespace App\Service;

use App\Ext\Service;
use Respect\Validation\Validator as v;
use App\Model\User as UserModel;
use App\Model\Product as ProductModel;
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
                    ProductOrderModel::CANCELED,
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
        $options = array(
            'conditions' => array('id = ? AND status = ?', $p['product_id'], ProductModel::AVAILABLE)
        );

            $product = ProductModel::find('first', $options);
        if (is_null($product)) {
            throw new \InvalidArgumentException("Available Product with id {$p['product_id']} is not found.");
        }

        try {
            UserModel::find($p['user_id']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("User with id {$p['user_id']} doesn't exist.");
        }

        return ProductOrderModel::create($p);
    }

    /**
     * Get list of pending orders for current user
     *
     * @param $user_id
     */
    protected function _pendingOrders($p) {
        try {
            $user = UserModel::find($p['user_id']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("User with id {$p['user_id']} doesn't exist.");
        }

        return $user->getPendingOrders();
    }

    /**
     * @param integer $user_id business owner id
     * @param integer $product_order_id
     * @param string  $status product order status
     */
    protected function _setOrderStatus($p) {
        try {
            $user = UserModel::find($p['user_id']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("User with id {$p['user_id']} doesn't exist.");
        }

        $options = array(
            'conditions' => array(
                'product_orders.id = ? AND p.status = ?',
                $p['product_order_id'],
                ProductModel::AVAILABLE
            ),
            'joins' => 'INNER JOIN products p ON p.id = product_id'
        );

        $order = ProductOrderModel::find($options);
        if (is_null($order)) {
            throw new \InvalidArgumentException("Product order with id {$p['product_order_id']} is unavailable.");
        }

        if (ProductOrderModel::CANCELED === $p['status']) {
            if ($user->id != $order->user_id) {
                throw new \Exception("Order can be canceled only by its submitter.");
            }
        } else if (!$user->isAbleToUpdate($order)) {
            throw new \Exception("User with id {$p['user_id']} doesn't have enough permissions.");
        }

        $order->status = $p['status'];
        $order->save();

        // Mark product as sold if order is approved
        if (ProductOrderModel::APPROVED == $p['status']) {
            $product = $order->product;
            $product->status = ProductModel::SOLD;
            $product->save();
        }

        return $order;
    }
}
