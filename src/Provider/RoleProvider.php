<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
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
 * @package App\Provider
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
        $provider = ProviderFactory::create(Role::class);
        if (isset($provider::$entities[$roleID]))
            return $provider::$entities[$roleID]->getCategory();
        $role = $provider->find($roleID);
        if ($role) {
            $provider::$entities[$roleID] = $role;
            return $role->getCategory();
        }
        return null;
    }
}