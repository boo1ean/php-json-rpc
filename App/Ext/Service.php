<?php
namespace App\Ext;

// @codeCoverageIgnoreStart

/**
 * Base service class
 */
class Service
{
    /**
     * DI container
     */
    protected $container;

    /**
     * Validation rules schema
     * 
     * @param string $name method name
     * @return array validation rules
     */
    public function validation() {
        return array();
    }

    /**
     * Initialize service dependencies
     *
     * @param  mixed $container application DI container
     * @return void
     */
    public function __construct($container) {
        $this->container = $container;
    }

    /**
     * Method call decoration
     *
     * @param  string $method name of called method
     * @param  array  $params method arguments
     * @return mixed  result of meethod call
     */
    public function __call($method, $params = array()) {
        $p = array_shift($params);

        if (!is_array($p)) {
            $p = array();
        }

        $this->validate($method, $p);

        $eventName = end(explode('\\', get_class($this))) . ".$method";

        try {
            $result = call_user_method('_' . $method, $this, $p);
        } catch (\Exception $e) {
            $this->container['vent']->emit("$eventName.error", array($this->container, $p, $e));
            throw $e;
        }

        $this->container['vent']->emit("$eventName.success", array($this->container, $p, $result));
        return $result;
    }

    /**
     * Retrives validation rules and applies it to method's params
     *
     * @param  sting $method name of method
     * @param  array $params method params
     * @return void
     * @throws \Exception when validation fails
     */
    public function validate($method, $params) {
        $rules = $this->validation();

        if (empty($rules[$method])) {
            return;
        }

        $rules = $rules[$method];
        foreach ($rules as $key => $rule) {
            $value = null;
            if (array_key_exists($key, $params)) {
                $value = $params[$key];
            }

            $result = $rule->validate($value);
            if (!$result) {
                try {
                    if ('' === $value) {
                        $value = null;
                    }

                    $rule->check($value);
                } catch (\InvalidArgumentException $e) {
                    $e->setName($key);
                    throw new \Exception($e->getMainMessage());
                }
            }
        }
    }

    /**
     * Translates rpp & page to limit and offset
     *
     * @param  array $p service method params
     * @return array limit and offset for query
     */
    public function pagination($p) {
        $offset = $p['rpp'] * ($p['page'] - 1);
        return array(
            'limit'  => $p['rpp'],
            'offset' => $offset
        );
    }
}
