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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * Class Permission
 * @package Kookaburra\SystemAdmin\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\SystemAdmin\Repository\PermissionRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="Permission",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="roleAction",columns={"role","action"})},
 *     indexes={@ORM\Index(name="role", columns={"role"}),
 *      @ORM\Index(name="action", columns={"action"})})
 * @UniqueEntity({"role","action"})
 */
class Permission implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(10) UNSIGNED ZEROFILL AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Role|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\SystemAdmin\Entity\Role", inversedBy="permissions")
     * @ORM\JoinColumn(name="role", referencedColumnName="id", nullable=false)
     * @MaxDepth(2)
     */
    private $role;

    /**
     * @var Action|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\SystemAdmin\Entity\Action", inversedBy="permissions")
     * @ORM\JoinColumn(name="action", referencedColumnName="id", nullable=false)
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

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }
}