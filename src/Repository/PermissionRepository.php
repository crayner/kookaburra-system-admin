<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */

namespace Kookaburra\SystemAdmin\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\ORM\ORMException;
use Kookaburra\SystemAdmin\Entity\Permission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Kookaburra\SystemAdmin\Entity\Role;

/**
 * Class PermissionRepository
 * @package Kookaburra\SystemAdmin\Repository
 */
class PermissionRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permission::class);
    }

    /**
     * findByRoleActionList
     * @param array $actionList
     * @param Role|null $role
     * @return array
     */
    public function findByRoleActionList(array $actionList, ?Role $role): array
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.action IN (:actionList)')
            ->leftJoin('p.role', 'r')
            ->setParameter('actionList', $actionList, Connection::PARAM_INT_ARRAY);
        if (!is_null($role))
            $query->andWhere('p.role = :role')
                ->setParameter('role', $role);
        return $query
            ->getQuery()
            ->getResult();
    }
}
