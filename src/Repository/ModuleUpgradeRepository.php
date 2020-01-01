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
 * Date: 16/10/2019
 * Time: 14:31
 */

namespace Kookaburra\SystemAdmin\Repository;

use Kookaburra\SystemAdmin\Entity\Module;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Kookaburra\SystemAdmin\Entity\ModuleUpgrade;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ModuleUpgradeRepository
 * @package Kookaburra\SystemAdmin\Repository
 */
class ModuleUpgradeRepository extends ServiceEntityRepository
{
    /**
     * ModuleUpgradeRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModuleUpgrade::class);
    }

    /**
     * deleteModuleRecords
     * @param Module $module
     */
    public function deleteModuleRecords(Module $module)
    {
        return $this->createQueryBuilder('mu')
            ->delete()
            ->where('mu.module = :module')
            ->setParameter('module', $module)
            ->getQuery()
            ->execute();
    }
}
