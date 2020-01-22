<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 21/01/2020
 * Time: 07:42
 */

namespace Kookaburra\SystemAdmin\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Kookaburra\SystemAdmin\Entity\Module;
use Kookaburra\SystemAdmin\Entity\ModuleUpgrade;

/**
 * Class UpgradeManager
 * @package App\Manager
 */
class UpgradeManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * UpgradeManager constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * moduleAtVersion
     * @param Module $module
     * @param string|null $version
     * @return bool
     */
    public function hasModuleVersion(Module $module, ?string $version): bool
    {
        if (in_array($version, [null,'']))
            return false;
        return $this->em->getRepository(ModuleUpgrade::class)->hasModuleVersion($module,$version);
    }

    /**
     * setModuleVersion
     * @param Module $module
     * @param string $version
     * @return $this
     */
    public function setModuleVersion(Module $module, string $version): self
    {
        if ($this->hasModuleVersion($module, $version)) {
            $mu = new ModuleUpgrade();

            $mu->setModule($module)->setVersion($version);
            $this->em->persist($mu);
            $this->em->flush();
        }
        return $this;
    }
}