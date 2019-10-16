<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 16/10/2019
 * Time: 14:31
 */

namespace Kookaburra\SystemAdmin\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Kookaburra\SystemAdmin\Entity\ModuleUpgrade;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class ModuleUpgradeRepository
 * @package Kookaburra\SystemAdmin\Repository
 */
class ModuleUpgradeRepository extends ServiceEntityRepository
{
    /**
     * ModuleUpgradeRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ModuleUpgrade::class);
    }
}
