<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 3/07/2019
 * Time: 14:57
 */

namespace Kookaburra\SystemAdmin\Provider;

use App\Provider\EntityProviderInterface;
use App\Provider\ProviderFactory;
use Kookaburra\SystemAdmin\Entity\Role;
use App\Manager\Traits\EntityTrait;

/**
 * Class RoleProvider
 * @package Kookaburra\SystemAdmin\Provider
 */
class RoleProvider implements EntityProviderInterface
{
    use EntityTrait;

    private $entityName = Role::class;

    /**
     * getRoleList
     * @param $roleList
     * @return mixed
     * @throws \Exception
     */
    public function getRoleList($roleList): array
    {
        $result = $this->getRepository()->getRoleList($roleList);

        foreach($result as $q=>$role)
        {
            $result[$q][0] = $role['id'];
            $result[$q][1] = $role['name'];
        }

        return $result;
    }

    /**
     * @var array|null
     */
    private static $entities;

    /**
     * getRoleCategory
     *
     * @param $roleID
     * @return string|null
     * @throws \Exception
     */
    public static function getRoleCategory($roleID): ?string
    {
        $roleID = intval($roleID);
        if (isset(self::$entities[$roleID]))
            return self::$entities[$roleID]->getCategory();
        $role = self::getRepository()->find($roleID);
        if ($role) {
            self::$entities[$roleID] = $role;
            return $role->getCategory();
        }
        return null;
    }

    /**
     * hasRole
     * @param string $role
     * @param array $roleList
     * @return bool
     */
    public function hasRole(string $role, array $roleList): bool
    {
        $role = $this->getRepository()->findOneByName($role);

        return in_array(intval($role->getId()), $roleList);
    }

    /**
     * findByCategory
     * @return array
     */
    public function findAllCategories(): array
    {
        $result = [];
        foreach($this->getRepository()->findAllCategories() as $name=>$item)
            $result[$name] = $name;
        return $result;
    }
}