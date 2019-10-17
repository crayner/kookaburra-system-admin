<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 20/07/2019
 * Time: 16:01
 */

namespace Kookaburra\SystemAdmin\Provider;

use App\Provider\EntityProviderInterface;
use Kookaburra\SystemAdmin\Entity\Action;
use App\Manager\Traits\EntityTrait;

/**
 * Class ActionProvider
 * @package App\Provider
 */
class ActionProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = Action::class;

    /**
     * findByURLListModuleRole
     * @param array $criteria
     * @return mixed
     * @throws \Exception
     */
    public function findByURLListModuleRole(array $criteria)
    {
        return $this->getRepository()->createQueryBuilder('a')
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
}