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
 * Date: 11/09/2019
 * Time: 11:44
 */

namespace Kookaburra\SystemAdmin\Validator;

use Symfony\Component\Validator\Constraint;

class NotificationListener extends Constraint
{
    /**
     * @return string
     */
    public function getTargets() : string{

        return self::CLASS_CONSTRAINT;
    }
}