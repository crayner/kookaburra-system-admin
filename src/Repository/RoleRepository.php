<?php
/**
 * Created by PhpStorm.
 *
 * Gibbon-Responsive
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * UserProvider: craig
 * Date: 23/11/2018
 * Time: 09:40
 */
namespace Kookaburra\SystemAdmin\Repository;

use App\Entity\Person;
use Kookaburra\SystemAdmin\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class RoleRepository
 * @package App\Repository
 */
class RoleRepository extends ServiceEntityRepository
{
    /**
     * RoleRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /**
     * findUserRoles
     * @param Person|null $person
     * @return array
     */
    public function findUserRoles(?Person $person): array
    {
        if (empty($person))
            return [];
        $roles = explode(',',$person->getAllRoles());
        $result = $this->createQueryBuilder('r')
            ->where('r.id IN (:roles)')
            ->setParameter('roles', $roles, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getResult();
        return $result ?: [];
    }

    /**
     * getRoleList
     * @param $roleList
     * @param $connection2
     * @return array
     */
    public function getRoleList($roleList): array
    {
        $roles = is_array($roleList) ? $roleList : explode(',',$roleList);
        return $this->createQueryBuilder('r')
            ->where('r.id IN (:roles)')
            ->setParameter('roles', $roles, Connection::PARAM_INT_ARRAY)
            ->select(['r.id', 'r.name'])
            ->getQuery()
            ->getResult();
    }

    /**
     * findByRoleIDList
     * @param array $list
     * @param string $key
     * @return array
     */
    public function findByRoleIDList(array $list, string $key): array
    {
        return $this->createQueryBuilder('r', 'r.'.$key)
            ->where('r.id in (:list)')
            ->select(['r.id','r.'.$key])
            ->setParameter('list', $list, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * findByRoleList
     * @param $list
     * @param $key
     * @return array
     */
    public function findByRoleList($list, $key): array
    {
        return $this->createQueryBuilder('r', 'r.id')
            ->where('r.' . $key . ' in (:list)')
            ->select(['r.id','r.' . $key])
            ->setParameter('list', $list, Connection::PARAM_STR_ARRAY)
            ->getQuery()
            ->getArrayResult();
    }
}
