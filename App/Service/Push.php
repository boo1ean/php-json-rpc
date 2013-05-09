<?php

namespace App\Service;

use App\Ext\Service;
use App\Model\Device as DeviceModel;
use App\Model\User as UserModel;

use Respect\Validation\Validator as v;
use Sly\NotificationPusher\Model\Message;

class Push extends Service
{
    public function validation() {
        return array(
            'notify' => array(
                'user_id' => v::notEmpty()->int()->positive(),
                'message' => v::notEmpty()->string()
            )
        );
    }

    protected function _notify($p) {
        try {
            $user = UserModel::find($p['user_id']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("User with id {$p['user_id']} doesn't exist.");
        }

        $devices = $user->devices;

        $ios = array_filter(array_map($this->deviceFilterFunction(DeviceModel::IOS), $devices));
        $this->push($this->container['apple-pusher'], $ios, $message);

        $android = array_filter(array_map($this->deviceFilterFunction(DeviceModel::ANDROID), $devices));
        $this->push($this->container['android-pusher'], $android, $message);
    }

    private function deviceFilterFunction($type) {
        return function($device) use ($type) {
            if ($device->type === $type) {
                return $device->token;
            }

            return false;
        };
    }

    /**
     * Send push notification to list of
     *
     * @param array $devices list of devices to send notifications
     * @param string $message
     */
    private function push($pusher, $devices, $message) {
        if (!$devices) {
            return null;
        }

        $message = new Message($message);

        $pusher->addMessage($message);
        $pusher->setDevices($devices);
        return $pusher->push();
    }
}
