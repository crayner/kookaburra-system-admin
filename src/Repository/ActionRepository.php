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

use Doctrine\DBAL\Driver\PDOException;
use Kookaburra\SystemAdmin\Entity\Action;
use Kookaburra\SystemAdmin\Entity\Module;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Kookaburra\UserAdmin\Util\UserHelper;

/**
 * Class ActionRepository
 * @package Kookaburra\SystemAdmin\Repository
 */
class ActionRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Action::class);
    }

    /**
     * findOneByURLListModuleNameRoleID
     * @param string $URLList
     * @param string $moduleName
     * @param string|null $roleID
     * @return array
     */
    public function findOneByURLListModuleNameRoleID(string $URLList, string $moduleName, string $roleID = null)
    {
        if ('' === $moduleName)
            return [];
        return $this->createQueryBuilder('a')
            ->leftJoin('a.module', 'm')
            ->leftJoin('a.permissions', 'p')
            ->where('a.URLList = :urlList')
            ->andWhere('p.role = :roleID')
            ->andWhere('m.name = :moduleName')
            ->setParameters(['urlList' => $URLList, 'moduleName' => $moduleName, 'roleID' => $roleID])
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * findOneByModuleContainsURL
     * @param Module $module
     * @param string $address
     * @return Action|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByModuleContainsURL(Module $module, string $address): ?Action
    {
        return $this->createQueryBuilder('a')
            ->where('a.module = :module')
            ->setParameter('module', $module)
            ->andWhere('a.URLList LIKE :route')
            ->setParameter('route', '%' . $address . '%')
            ->orderBy('a.precedence', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @var Action[]
     */
    private $actions;

    /**
     * findOneByNameModule
     * @param string $name
     * @param Module $module
     * @return Action|null
     * @throws NonUniqueResultException
     */
    public function findOneByNameModule(string $name, Module $module): ?Action
    {
        $this->actions = $this->actions ?: $this->findAll();

        foreach($this->actions as $action)
            if ($action->getName() === $name && $action->getModule()->isEqualTo($module))
                return $action;

        return $this->createQueryBuilder('a')
            ->where('a.name = :name')
            ->andWhere('a.module = :module')
            ->setParameters(['name' => $name, 'module' => $module])
            ->orderBy('a.precedence', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * findByURLListModuleRole
     * @param array $criteria
     * @return mixed
     */
    public function findByURLListModuleRole(array $criteria)
    {
        return $this->createQueryBuilder('a')
            ->join('a.permissions', 'p')
            ->join('p.role', 'r')
            ->where('a.URLList LIKE :name')
            ->andWhere('a.module = :module')
            ->andWhere('p.role = :role')
            ->andWhere('a.name LIKE :sub')
            ->setParameters($criteria)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * findHighestGroupedAction
     * @param string $route
     * @param Module $module
     * @return bool
     */
    public function findHighestGroupedAction(string $route, Module $module)
    {
        try {
            return $this->createQueryBuilder('a')
            ->select('a.name')
            ->join('a.permissions', 'p')
            ->where('a.URLList LIKE :actionName')
            ->setParameter('actionName', '%'.$route.'%')
            ->andWhere('a.module = :module')
            ->setParameter('module', $module)
            ->andWhere('p.role = :currentRole')
            ->setParameter('currentRole', UserHelper::getCurrentUser()->getPrimaryRole())
            ->orderBy('a.precedence', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        } catch (NonUniqueResultException | PDOException | \PDOException $e) {
            return null;
        }
    }

    /**
     * findOneByRoute
     * @param string $route
     * @return Action|null
     */
    public function findOneByRoute(string $route): ?Action
    {
        if (mb_strpos($route, '__') !== false) {
            $module = explode('__', $route);
            $route = $module[1];
            $module = ucwords(str_replace('_', ' ', $module[0]));
            try {
                return $this->createQueryBuilder('a')
                    ->where('a.URLList LIKE :route')
                    ->join('a.module', 'm')
                    ->andWhere('m.name = :module')
                    ->setParameter('module', $module)
                    ->setParameter('route', '%' . $route . '%')
                    ->orderBy('a.precedence', 'ASC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();
            } catch (NonUniqueResultException $e) {
                return null;
            }
        }

        try {
            return $this->createQueryBuilder('a')
                ->where('a.URLList LIKE :route')
                ->setParameter('route', '%' . $route . '%')
                ->orderBy('a.precedence', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
