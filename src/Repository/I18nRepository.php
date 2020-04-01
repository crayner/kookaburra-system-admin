<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * UserProvider: craig
 * Date: 23/11/2018
 * Time: 11:58
 */
namespace Kookaburra\SystemAdmin\Repository;

use Kookaburra\SystemAdmin\Entity\I18n;
use Kookaburra\SystemAdmin\Util\LocaleHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class I18nRepository
 * @package Kookaburra\SystemAdmin\Repository
 */
class I18nRepository extends ServiceEntityRepository
{
    /**
     * @var string|null
     */
    private $locale;

    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, I18n::class);
    }

    /**
     * findSystemDefaultCode
     * @return string|null
     */
    public function findSystemDefaultCode(): ?string
    {
        $systemDefault = $this->findOneBySystemDefault('Y');
        return $systemDefault ? $systemDefault->getCode() : null;
    }

    /**
     * findLocaleRightToLeft
     * @return bool
     * @throws \Exception
     */
    public function findLocaleRightToLeft(): bool
    {
        if (null === $this->locale)
            $this->locale = LocaleHelper::getLocale();

        $lang = $this->findOneByCode($this->locale);

        return $lang ? $lang->isRtl() : false;
    }

    /**
     * findByActive
     * @return array
     */
    public function findByActive(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.active = :yes')
            ->andWhere('i.installed = :yes')
            ->orWhere('i.systemDefault = :yes')
            ->orderby('i.systemDefault', 'DESC')
            ->addOrderBy('i.name', 'ASC')
            ->setParameter('yes', 'Y')
            ->getQuery()
            ->getResult();
    }
}
