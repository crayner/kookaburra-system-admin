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
 * Time: 09:33
 */
namespace Kookaburra\SystemAdmin\Entity;

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Role
 * @package Kookaburra\SystemAdmin\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\SystemAdmin\Repository\RoleRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="Role", uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"}), @ORM\UniqueConstraint(name="nameShort", columns={"nameShort"})})
 */
class Role implements EntityInterface
{
    use BooleanList;
    
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", name="gibbonRoleID", columnDefinition="INT(3) UNSIGNED ZEROFILL")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @return array
     */
    public static function getRestrictionList(): array
    {
        return self::$restrictionList;
    }

    /**
     * @return array
     */
    public static function getTypeList(): array
    {
        return self::$typeList;
    }

    /**
     * @return array
     */
    public static function getCategoryList(): array
    {
        return self::$categoryList;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Role
     */
    public function setId(?int $id): Role
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(length=8, options={"default": "Staff"})
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getCategoryList")
     */
    private $category;

    /**
     * @var array
     */
    private static $categoryList = ['Staff','Student','Parent','Other'];

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category ?: '';
    }

    /**
     * setCategory
     * @param string $category
     * @return Role
     */
    public function setCategory(string $category): Role
    {
        $this->category = in_array($category, self::getCategoryList()) ? $category : 'Staff';
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(length=20, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=20)
     */
    private $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?: '';
    }

    /**
     * setName
     * @param string $name
     * @return Role
     */
    public function setName(string $name): Role
    {
        $this->name = mb_substr($name, 0, 20);
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(length=4, name="nameShort", unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=4)
     */
    private $nameShort;

    /**
     * @return string
     */
    public function getNameShort(): string
    {
        return $this->nameShort ?: '';
    }

    /**
     * setNameShort
     * @param string $nameShort
     * @return Role
     */
    public function setNameShort(string $nameShort): Role
    {
        $this->nameShort = mb_substr($nameShort, 0, 4);
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(length=60, name="description")
     * @Assert\NotBlank()
     * @Assert\Length(max=60)
     */
    private $description;

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description ?: '';
    }

    /**
     * setDescription
     * @param string $description
     * @return Role
     */
    public function setDescription(string $description): Role
    {
        $this->description = mb_substr($description, 0, 60);
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(length=4, name="type", options={"default": "Core"})
     * @Assert\Choice(callback="getTypelist")
     */
    private $type = 'Additional';

    /**
     * @var array
     */
    private static $typeList = ['Core', 'Additional'];

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * setType
     * @param string $type
     * @return Role
     */
    public function setType(string $type): Role
    {
        $this->type = in_array($type, self::getTypeList()) ? $type : 'Additional';
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(length=1, name="canLoginRole", options={"default": "Y"})
     */
    private $canLoginRole = 'Y';

    /**
     * @return boolean
     */
    public function isCanLoginRole(): bool
    {
        return $this->canLoginRole === 'Y';
    }

    /**
     * @return string
     */
    public function getCanLoginRole(): string
    {
        return $this->canLoginRole;
    }

    /**
     * setCanLoginRole
     * @param string $canLoginRole
     * @return Role
     */
    public function setCanLoginRole(string $canLoginRole): Role
    {
        $this->canLoginRole = self::checkBoolean($canLoginRole);
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(length=1, name="futureYearsLogin", options={"default": "Y"})
     */
    private $futureYearsLogin = 'Y';

    /**
     * @return boolean
     */
    public function isFutureYearsLogin(): bool
    {
        return $this->futureYearsLogin === 'Y';
    }

    /**
     * @return string
     */
    public function getFutureYearsLogin(): string
    {
        return self::checkBoolean($this->futureYearsLogin);
    }

    /**
     * setFutureYearsLogin
     * @param string $futureYearsLogin
     * @return Role
     */
    public function setFutureYearsLogin(string $futureYearsLogin): Role
    {
        $this->futureYearsLogin = self::checkBoolean($futureYearsLogin);
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(length=1, name="pastYearsLogin", options={"default": "Y"})
     */
    private $pastYearsLogin = 'Y';

    /**
     * @return boolean
     */
    public function isPastYearsLogin(): bool
    {
        return $this->pastYearsLogin === 'Y';
    }

    /**
     * @return string
     */
    public function getPastYearsLogin(): string
    {
        return self::checkBoolean($this->pastYearsLogin);
    }

    /**
     * setPastYearsLogin
     * @param string $pastYearsLogin
     * @return Role
     */
    public function setPastYearsLogin(string $pastYearsLogin): Role
    {
        $this->pastYearsLogin = self::checkBoolean($pastYearsLogin);
        return $this;
    }

    /**
     * @var string
     * @ORM\Column(length=10, name="restriction", options={"default": "None"})
     * @Assert\Choice(callback="getRestrictionList")
     */
    private $restriction = 'None';

    /**
     * @var array
     */
    private static $restrictionList = ['None', 'Same Role', 'Admin Only'];

    /**
     * @return string
     */
    public function getRestriction(): string
    {
        return $this->restriction;
    }

    /**
     * setRestriction
     * @param string $restriction
     * @return Role
     */
    public function setRestriction(string $restriction): Role
    {
        $this->restriction = in_array($restriction, self::getRestrictionList()) ? $restriction : 'None';
        return $this;
    }

    public function __toString(): string
    {
        return $this->getCategory().': '.$this->getName();
    }

    /**
     * @var Collection|Permission[]|null
     * @ORM\OneToMany(targetEntity="Kookaburra\SystemAdmin\Entity\Permission", mappedBy="role")
     */
    private $permissions;

    /**
     * @return Collection|Permission[]|null
     */
    public function getPermissions()
    {
        return $this->permissions = $this->permissions ?: new ArrayCollection();
    }

    /**
     * Permissions.
     *
     * @param Collection|Permission[]|null $permissions
     * @return Role
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'category' => $this->getCategory(),
            'name' => $this->getName(),
            'name_short' => $this->getNameShort(),
            'description' => $this->getDescription(),
            'type' => $this->getType(),
            'login_years' => $this->getLoginYearsDescription(),
            'isAdditional' => $this->getType() !== 'Core',
        ];
    }

    /**
     * getLoginYearsDescription
     * @return string
     */
    public function getLoginYearsDescription(): string
    {
        if (!$this->isCanLoginRole()) {
            return 'None';
        } elseif ($this->isFutureYearsLogin() && $this->isPastYearsLogin()) {
            return 'All years';
        } elseif (!$this->isFutureYearsLogin() && !$this->isPastYearsLogin()) {
            return 'Current year only';
        } elseif (!$this->isFutureYearsLogin()) {
            return 'Current/past years only';
        } elseif (!$this->isPastYearsLogin()) {
            return 'Current/future years only';
        }

    }
}