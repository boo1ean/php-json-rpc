<?php
namespace App\Ext;
use Sly\NotificationPusher\Pusher\AndroidPusher as BaseAndroidPusher;

// @codeCoverageIgnoreStart

/**
 * Add opportunity to set devices via method
 */
class AndroidPusher extends BaseAndroidPusher
{
    /**
     * Set devices to config
     *
     * @param array $devices list of devices
     */
    public function setDevices($devices) {
        if (empty($devices) || is_null($devices)) {
            throw new ConfigurationException('You must give an array of devices UUIDs to the pusher');
        }

        $this->config['devices'] = $devices;
    }
}
