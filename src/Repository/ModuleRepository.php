<?php
/**
 * Created by PhpStorm.
 *
 * Gibbon-Responsive
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace Kookaburra\SystemAdmin\Repository;

use Kookaburra\SystemAdmin\Entity\Module;
use Kookaburra\SystemAdmin\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class ModuleRepository
 * @package App\Repository
 */
class ModuleRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Module::class);
    }

    /**
     * findModulesByRole
     * @param int $roleID
     * @return array|null
     */
    public function findModulesByRole(int $roleID)
    {
        return $this->createQueryBuilder('m')
            ->select(['m.category', 'm.name', 'm.type', 'm.entryURL', 'a.entryURL AS alternateEntryURL'])
            ->join('m.actions', 'a')
            ->join('a.permissions', 'p')
            ->join('p.role', 'r')
            ->where('m.active = :active')
            ->andWhere('a.menuShow = :active')
            ->andWhere('r.id = :role_id')
            ->groupBy('m.name')
            ->orderBy('m.name')
            ->addOrderBy('a.name')
            ->setParameter('active', 'Y')
            ->setParameter('role_id', intval($roleID))
            ->getQuery()
            ->getResult();
    }

    /**
     * findModuleActionsByRole
     * @param Module $module
     * @param Role $role
     * @return mixed
     */
    public function findModuleActionsByRole(Module $module, Role $role)
    {
        $result = $this->createQueryBuilder('m')
            ->select(['a.category', 'm.name AS moduleName', 'a.name AS actionName', 'm.type', 'a.precedence', 'm.entryURL AS moduleEntry', 'a.entryURL', 'a.URLList', 'a.name AS name'])
            ->join('m.actions', 'a')
            ->join('a.permissions', 'p')
            ->where('m.id = :module_id')
            ->setParameter('module_id', intval($module->getId()))
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->andWhere('a.entryURL != :empty')
            ->setParameter('empty', '')
            ->andWhere('a.menuShow = :yes')
            ->setParameter('yes', 'Y')
            ->groupBy('a.name')
            ->orderBy('a.category')
            ->addOrderBy('a.name')
            ->addOrderBy('a.precedence', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * findFastFinderActions
     * @param Role $role
     * @return mixed
     */
    public function findFastFinderActions(Role $role, string $actionTitle)
    {
        return $this->createQueryBuilder('m')
            ->select([
                "CONCAT('Act-', m.name, '/', a.entryURL) AS id",
                "CONCAT('".$actionTitle."', SUBSTRING_INDEX(a.name, '_', 1)) AS text",
                'm.name as search'
            ])
            ->join('m.actions', 'a')
            ->join('a.permissions', 'p')
            ->where('m.active = :yes')
            ->andWhere('a.menuShow = :yes')
            ->andWhere('p.role = :role')
            ->orderBy('text', 'ASC')
            ->setParameters(['yes' => 'Y', 'role' => $role])
            ->distinct()
            ->getQuery()
            ->getResult();
    }
}
