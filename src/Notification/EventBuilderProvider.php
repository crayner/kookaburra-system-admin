<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 25/10/2019
 * Time: 15:24
 */

namespace Kookaburra\SystemAdmin\Notification;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class EventBuilderProvider
 *
 * You need to seed this class by adding it to a called class prior to use, so that the translator and twig environment are set.
 * @package Kookaburra\SystemAdmin\Notification
 */
class EventBuilderProvider
{
    /**
     * @var NotificationSender
     */
    private static $sender;

    /**
     * EventBuilderProvider constructor.
     */
    public function __construct(NotificationSender $sender)
    {
        self::$sender = $sender;
    }

    /**
     * create
     * @param string $module
     * @param string $event
     * @return EventBuilder
     */
    public static function create(string $module, string $event): EventBuilder
    {
        $event = new EventBuilder($module, $event);
        return $event;
    }

    /**
     * addEvent
     * @param EventBuilder $event
     * @return NotificationSender
     */
    public static function addEvent(EventBuilder $event): NotificationSender
    {
        return self::$sender->addEvent($event);
    }
}