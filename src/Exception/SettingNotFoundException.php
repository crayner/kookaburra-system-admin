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
 * Date: 26/07/2019
 * Time: 10:17
 */

namespace Kookaburra\SystemAdmin\Exception;

use Throwable;

/**
 * Class SettingNotFoundException
 * @package Kookaburra\SystemAdmin\Exception
 */
class SettingNotFoundException extends \RuntimeException
{
    public function __construct(string $scope, string $name, string $message = "")
    {
        if ('' === $message)
            $message = sprintf('The Setting defined by "%s:%s" was not found.', $scope, $name);

        parent::__construct($message);
    }
}