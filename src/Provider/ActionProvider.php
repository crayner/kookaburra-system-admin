<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 20/07/2019
 * Time: 16:01
 */

namespace Kookaburra\SystemAdmin\Provider;

use App\Provider\EntityProviderInterface;
use App\Manager\Traits\EntityTrait;
use Kookaburra\SystemAdmin\Entity\Action;

/**
 * Class ActionProvider
 * @package Kookaburra\SystemAdmin\Provider
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
        return $this->getRepository()->findByURLListModuleRole($criteria);
    }
}