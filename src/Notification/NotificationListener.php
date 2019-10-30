<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 25/10/2019
 * Time: 16:02
 */

namespace Kookaburra\SystemAdmin\Notification;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class NotificationListener
 * @package Kookaburra\SystemAdmin\Notification
 */
class NotificationListener implements EventSubscriberInterface
{

    /**
     * @var NotificationSender
     */
    private $notificationSender;

    /**
     * NotificationListener constructor.
     * @param NotificationSender $notificationSender
     */
    public function __construct(NotificationSender $notificationSender)
    {
        $this->notificationSender = $notificationSender;
    }

    /**
     * getSubscribedEvents
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [ 'sendNotifications' ]
        ];
    }

    /**
     * sendNotifications
     */
    public function sendNotifications()
    {
        if ($this->notificationSender->hasEvents())
        {
            $this->notificationSender->renderEvents();
        }
    }
}