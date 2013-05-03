<?php
namespace App\Rpc;
class Methods
{
    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function login($p = array()) {
        $result = $this->container['user-service']->login($p);
        if (!$result) {
            throw new \Exception('Invalid email or password.');
        }

        return $this->container['user']->to_json();
    }

    public function userBusinesses($p = array()) {
        $user = $this->container['user']; 

        if (is_null($user)) {
            throw new \Exception('Unauthorized');
        }

        $businesses = $user->businesses;
        return $this->json($businesses);
    }

    /**
     * Get list of all businesses
     */
    public function businesses($p = array()) {
        $defaults = array(
            'rpp' => 20,
            'page' => 1
        );

        $p = array_merge($defaults, $p);

        $businesses = $this->container['business-service']->getBusinesses($p);
        return $this->json($businesses);
    }

    /**
     * Get list of product for specified business
     *
     * @param integer $business_id
     **/
    public function products($p = array()) {
        $products = $this->container['product-service']->getProducts($p);
        return $this->json($products);
    }

    public function logout() {
        $this->container['auth-service']->clearIdentity();
        return true;
    }

    /**
     * Serialize array of activerecords to json
     *
     * @param array $models array of models
     * @return string json encoded collection
     */
    public function json($models = array()) {
        $result = array();
        foreach ($models as $model) {
            $result[] = $model->attributes();
        }

        return json_encode($result);
    }
}
