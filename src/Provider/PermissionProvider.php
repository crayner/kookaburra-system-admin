<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 4/12/2019
 * Time: 15:12
 */

namespace Kookaburra\SystemAdmin\Provider;

use App\Manager\Traits\EntityTrait;
use App\Provider\EntityProviderInterface;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\ORM\ORMException;
use Kookaburra\SystemAdmin\Entity\Action;
use Kookaburra\SystemAdmin\Entity\Permission;
use Kookaburra\SystemAdmin\Entity\Role;
use Kookaburra\UserAdmin\Entity\PermissionSearch;

/**
 * Class PermissionProvider
 * @package Kookaburra\SystemAdmin\Provider
 */
class PermissionProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = Permission::class;

    /**
     * searchPermissions
     * @param PermissionSearch $search
     */
    public function searchPermissions(PermissionSearch $search): array
    {
        if (is_null($search->getModule()))
            $result = $this->getRepository(Action::class)->findBy([], ['name' => 'ASC']);
        else
            $result = $this->getRepository(Action::class)->findByModule($search->getModule(), ['name' => 'ASC']);

        $actions = [];
        $w = [];
        foreach($result as $item)
        {
            $action = [];
            $action['id'] = $item->getId();
            $action['name'] = $item->getName();
            $action['description'] = $item->getDescription();
            $actions[$item->getId()] = $action;
            $w[] = $item->getId();
        }

        $permissions = $this->getRepository()->findByRoleActionList($w, $search->getRole());

        foreach($permissions as $w)
        {
            if (isset($actions[$w->getAction()->getId()])) {
                $actions[$w->getAction()->getId()]['roles'] = isset($actions[$w->getAction()->getId()]['roles']) ? $actions[$w->getAction()->getId()]['roles'] : [];
                $actions[$w->getAction()->getId()]['roles'][] = $w->getRole();
            }
        }

        return $actions;
    }

    /**
     * duplicatePermissions
     * @param Role $parent
     * @param Role $role
     * @param array $data
     * @return array
     */
    public function duplicatePermissions(Role $parent, Role $role, array $data = []): array
    {
        $perms = $this->getRepository()->findByRole($parent);
        $em = $this->getEntityManager();
        try {
            $em->beginTransaction();
            foreach ($perms as $w) {
                $q = clone $w;
                $q->setId(null)->setRole($role);
                $em->persist($q);
            }
            $em->flush();
            $em->commit();
        } catch (PDOException | \PDOException | ORMException $e) {
            $data['errors'][] = ['class' => 'error', 'message' => 'return.error.1'];
            $data['status'] = 'error';
        }
        return $data;
    }
}