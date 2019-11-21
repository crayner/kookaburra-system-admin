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
namespace Kookaburra\SystemAdmin\Entity;

use App\Manager\EntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class Permission
 * @package Kookaburra\SystemAdmin\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\SystemAdmin\Repository\PermissionRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="Permission", indexes={@ORM\Index(name="gibbonRoleID", columns={"gibbonRoleID"}), @ORM\Index(name="gibbonActionID", columns={"gibbonActionID"})})
 */
class Permission implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", name="permissionID", columnDefinition="INT(10) UNSIGNED ZEROFILL AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Role|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\SystemAdmin\Entity\Role", inversedBy="permissions")
     * @ORM\JoinColumn(name="gibbonRoleID", referencedColumnName="gibbonRoleID", nullable=false)
     * @MaxDepth(2)
     */
    private $role;

    /**
     * @var Action|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\SystemAdmin\Entity\Action", inversedBy="permissions")
     * @ORM\JoinColumn(name="gibbonActionID", referencedColumnName="gibbonActionID", nullable=false)
     * @MaxDepth(2)
     */
    private $action;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Permission
     */
    public function setId(?int $id): Permission
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Role|null
     */
    public function getRole(): ?Role
    {
        return $this->role;
    }

    /**
     * @param Role|null $role
     * @return Permission
     */
    public function setRole(?Role $role): Permission
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return Action|null
     */
    public function getAction(): ?Action
    {
        return $this->action;
    }

    /**
     * @param Action|null $action
     * @return Permission
     */
    public function setAction(?Action $action): Permission
    {
        $this->action = $action;
        return $this;
    }
}