<?php
namespace App\Rpc;
class Methods
{
    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function login($params) {
        $result = $this->container['user-service']->login($params);
        if (!$result) {
            throw new \Exception('Invalid email or password.');
        }

        return $this->container['user']->to_json();
    }

    public function businesses() {
        $user = $this->container['user']; 

        if (is_null($user)) {
            throw new \Exception('Unauthorized');
        }

        $businesses = $user->businesses;
        return $this->json($businesses);
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
    public function json($models) {
        $result = array();
        foreach ($models as $model) {
            $result[] = $model->attributes();
        }

        return json_encode($result);
    }
}
