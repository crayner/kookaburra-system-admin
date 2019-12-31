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
 * Date: 17/10/2019
 * Time: 13:08
 */

namespace Kookaburra\SystemAdmin\Manager;

/**
 * Interface ModuleInstallationInterface
 * @package Kookaburra\SystemAdmin\Manager
 */
interface ModuleInstallationInterface
{
    public function up();
    public function down();
}