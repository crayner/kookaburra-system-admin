<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 22/11/2019
 * Time: 09:40
 */

namespace Kookaburra\SystemAdmin\Manager;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken as BaseToken;
use Symfony\Component\Security\Guard\Token\GuardTokenInterface;

class UsernamePasswordToken extends BaseToken implements GuardTokenInterface
{

}