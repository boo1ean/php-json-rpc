<?php
namespace App;

// TODO add uses
// @codeCoverageIgnoreStart
class Application
{
    const V2 = '/v2';

    /**
     * Config array
     */
    protected $config;

    /**
     * DI container
     */
    protected $c;

    /**
     * Request query string
     */
    protected $query = '';

    /**
     * @param array config application config array
     * @return void
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Run JSON-RPC 2.0 application
     * @return \App\Application
     */
    public function runRPC() {
        $this->c['json-rpc-server']->process();
        return $this;
    }

    /**
     * Setup application
     * @return void
     */
    public function setup() {
        if (!empty($_SERVER['REQUEST_URI'])) {
            $this->query = $_SERVER['REQUEST_URI'];
        }

        $this->setupDb();
        $this->setupContainer();
        $this->setupEvents();
        return $this;
    }

    /**
     * Get DI container
     * @return Pimple di container
     */
    public function getContainer() {
        return $this->c;
    }

    /**
     * Setup database connection
     */
    protected function setupDb() {
        $connections = $this->config['db']['connections'];
        \ActiveRecord\Config::initialize(function($cfg) use ($connections) {
            $cfg->set_model_directory(APP_PATH . '/Model');
            $cfg->set_connections($connections);
        });
    }

    /**
     * Setup di container
     */
    protected function setupContainer() {
        $this->c = new \Pimple();

        if (self::V2 == $this->query) {
            $this->c['rpc-methods-class'] = '\\App\\Rpc\\MethodsV2';
        } else {
            $this->c['rpc-methods-class'] = '\\App\\Rpc\\Methods';
        }

        $this->c['config'] = $this->config;

        $this->c['vent'] = $this->c->share(function($c) {
            return new \Evenement\EventEmitter();
        });

        $this->c['rpc-methods'] = function($c){
            return new $c['rpc-methods-class']($c);
        };

        $this->c['json-rpc-server'] = $this->c->share(function($c) {
            return new \Junior\Server($c['rpc-methods']);
        });

        $this->c['auth-storage'] = function($c) {
            return new \Zend\Authentication\Storage\Session();
        };

        $this->c['auth-adapter'] = function($c) {
            return new \App\Ext\Zend\Auth\Adapter();
        };

        $this->c['auth-service'] = $this->c->share(function($c) {
            return new \Zend\Authentication\AuthenticationService($c['auth-storage'], $c['auth-adapter']);
        });

        $this->c['user'] = $this->c->share(function($c) {
            return $c['auth-service']->getIdentity();
        });

        $this->c['apple-pusher'] = $this->c->share(function($c) {
            return new \App\Ext\ApplePusher($c['config']['apple_push_notifications']);
        });

        $this->c['android-pusher'] = $this->c->share(function($c) {
            return new \App\Ext\AndroidPusher($c['config']['android_push_notifications']);
        });

        $this->c['user-service'] = $this->c->share(function($c) {
            return new \App\Service\User($c);
        });

        $this->c['business-service'] = $this->c->share(function($c) {
            return new \App\Service\Business($c);
        });

        $this->c['product-service'] = $this->c->share(function($c) {
            return new \App\Service\Product($c);
        });

        $this->c['booking-service'] = $this->c->share(function($c) {
            return new \App\Service\Booking($c);
        });

        $this->c['order-service'] = $this->c->share(function($c) {
            return new \App\Service\Order($c);
        });

        $this->c['review-service'] = $this->c->share(function($c) {
            return new \App\Service\Review($c);
        });

        $this->c['push-service'] = $this->c->share(function($c) {
            return new \App\Service\Push($c);
        });
    }

    protected function setupEvents() {
        $this->c['vent']->on('Booking.requestBooking.success', function($c, $p, $productBooking) {
            $owner = $productBooking->booking->product->business->user;

            $p = array(
                'user_id' => $owner->id,
                'message' => json_encode($productBooking->attributes())
            );

            $c['push-service']->notify($p);
        });

        $this->c['vent']->on('Order.requestOrder.success', function($c, $p, $order) {
            $owner = $order->product->business->user;

            $p = array(
                'user_id' => $owner->id,
                'message' => json_encode($order->attributes())
            );

            $c['push-service']->notify($p);
        });

        $this->c['vent']->on('Booking.setStatus.success', function($c, $p, $productBooking) {
            if ($productBooking->status == \App\Model\ProductBooking::CANCELED) {
                return;
            }

            $customer = $productBooking->user;

            $p = array(
                'user_id' => $customer->id,
                'message' => json_encode($productBooking->attributes())
            );

            $c['push-service']->notify($p);
        });

        $this->c['vent']->on('Order.setOrderStatus.success', function($c, $p, $order) {
            if ($order->status == \App\Model\ProductOrder::CANCELED) {
                return;
            }

            $customer = $order->user;

            $p = array(
                'user_id' => $customer->id,
                'message' => json_encode($order->attributes())
            );

            $c['push-service']->notify($p);
        });
    }
}
