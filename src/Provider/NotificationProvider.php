<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 6/08/2019
 * Time: 15:06
 */

namespace Kookaburra\SystemAdmin\Provider;

use App\Manager\Traits\EntityTrait;
use App\Provider\EntityProviderInterface;
use Kookaburra\SystemAdmin\Entity\Notification;

/**
 * Class NotificationProvider
 * @package Kookaburra\SystemAdmin\Provider
 */
class NotificationProvider implements EntityProviderInterface
{
    use EntityTrait;
    /**
     * @var string
     */
    private $entityName = Notification::class;
}