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
use App\Manager\Traits\BooleanList;
use App\Util\TranslationsHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Module
 * @package Kookaburra\SystemAdmin\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\SystemAdmin\Repository\ModuleRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="Module", uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"})})
 * @UniqueEntity({"name"})
 * */
class Module implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(4) UNSIGNED AUTO_INCREMENT", options={"comment": "This number is assigned at install, and is only unique to the installation"})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=30, options={"comment": "This name should be globally unique preferably, but certainly locally unique"})
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var string|null
     * @ORM\Column(name="entryURL", options={"default": "index.php"})
     */
    private $entryURL;

    /**
     * @var string|null
     * @ORM\Column(length=12, options={"default": "Core"})
     */
    private $type = 'Core';

    /**
     * @var array
     */
    private static $typeList = ['Core', 'Additional'];

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     */
    private $active = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=10)
     */
    private $category;

    /**
     * @var string|null
     * @ORM\Column(length=6)
     */
    private $version;

    /**
     * @var string|null
     * @ORM\Column(length=40)
     */
    private $author;

    /**
     * @var string|null
     * @ORM\Column()
     */
    private $url;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Kookaburra\SystemAdmin\Entity\Action", mappedBy="module", orphanRemoval=true)
     */
    private $actions;

    /**
     * @var null|string
     */
    private $status;

    /**
     * @var bool
     */
    private $updateRequired = false;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Kookaburra\SystemAdmin\Entity\ModuleUpgrade",mappedBy="module",orphanRemoval=true)
     * @ORM\OrderBy({"version" = "DESC"})
     */
    private $upgradeLogs;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Kookaburra\SystemAdmin\Entity\NotificationEvent", mappedBy="module", orphanRemoval=true)
     */
    private $events;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Kookaburra\SystemAdmin\Entity\Notification", mappedBy="module", orphanRemoval=true)
     */
    private $notifications;

    /**
     * Module constructor.
     */
    public function __construct()
    {
        $this->upgradeLogs = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->notifications = new ArrayCollection();
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
     * @return Module
     */
    public function setId(?int $id): Module
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Module
     */
    public function setName(?string $name): Module
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Module
     */
    public function setDescription(?string $description): Module
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEntryURL(): ?string
    {
        return $this->entryURL;
    }

    /**
     * @param string|null $entryURL
     * @return Module
     */
    public function setEntryURL(?string $entryURL): Module
    {
        $this->entryURL = $entryURL;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type = in_array($this->type, self::getTypeList()) ? $this->type : 'Core';
    }

    /**
     * @param string|null $type
     * @return Module
     */
    public function setType(?string $type): Module
    {
        $this->type = in_array($type, self::getTypeList()) ? $type : 'Core' ;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getActive(): ?string
    {
        return $this->active;
    }

    /**
     * @param string|null $active
     * @return Module
     */
    public function setActive(?string $active): Module
    {
        $this->active = self::checkBoolean($active);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string|null $category
     * @return Module
     */
    public function setCategory(?string $category): Module
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * @param string|null $version
     * @return Module
     */
    public function setVersion(?string $version): Module
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @param string|null $author
     * @return Module
     */
    public function setAuthor(?string $author): Module
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return Module
     */
    public function setUrl(?string $url): Module
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return array
     */
    public static function getTypeList(): array
    {
        return self::$typeList;
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(?string $name = NULL): array
    {
        return [
            'gibbonModuleID' => $this->id,
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'entryURL' => $this->entryURL,
            'type' => $this->getType(),
            'active' => $this->active,
            'category' => $this->category,
            'version' => $this->version,
            'author' => $this->author,
            'url' => $this->url,
            'status' => $this->getStatus(),
            'updateRequired' => $this->isUpdateRequired(),
            'isNotCore' => $this->getType() !== 'Core',
        ];
    }

    /**
     * @return Collection
     */
    public function getActions(): Collection
    {
        if (null === $this->actions)
            $this->actions = new ArrayCollection();

        if ($this->actions instanceof PersistentCollection)
            $this->actions->initialize();

        return $this->actions ;
    }

    /**
     * Actions.
     *
     * @param Collection $actions
     * @return Module
     */
    public function setActions(Collection $actions): Module
    {
        $this->actions = $actions;
        return $this;
    }

    /**
     * getEntryURLFullRoute
     * @return string
     */
    public function getEntryURLFullRoute(?string $entryURL = null): string
    {
        return Action::getRouteName($this->getName(), ($entryURL ?: $this->getEntryURL()));
    }

    /**
     * isEqualTo
     * @param Module $user
     * @return true
     */
    public function isEqualTo(Module $module): bool
    {
        if ($module->getId() !== $this->getId())
            return false;

        return true;
    }

    /**
     * getModuleDir
     * @return string
     */
    private function getModuleDir(): string
    {
        return realpath(__DIR__ . '/../../vendor/kookaburra') ?: '';
    }

    /**
     * @return bool|null
     */
    public function getStatus(): string
    {

        if (null === $this->status) {
            if ($this->getType() === 'Core') {
                $this->status = TranslationsHelper::translate('Installed');
            } else {
                if (false === is_dir($this->getModuleDir() . '/' . str_replace(' ', '-', strtolower($this->getName()))))
                {
                    $this->status = TranslationsHelper::translate('Not Installed');
                } else {
                    $installed = $this->getUpgradeLogs()->filter(function($log) {
                        return $log->getVersion() === 'Installation';
                    });
                    if ($this->getUpgradeLogs()->count() === 0 || $installed->count() === 0)
                        $this->status = TranslationsHelper::translate('Not Installed');
                    else
                        $this->status = TranslationsHelper::translate('Installed');
                }
            }
        }
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isUpdateRequired(): bool
    {
        return $this->updateRequired ? true : false;
    }

    /**
     * UpdateRequired.
     *
     * @param bool $updateRequired
     * @return Module
     */
    public function setUpdateRequired(bool $updateRequired): Module
    {
        $this->updateRequired = $updateRequired;
        return $this;
    }

    /**
     * getUpgradeLogs
     * @return Collection
     */
    public function getUpgradeLogs(): Collection
    {
        return $this->upgradeLogs = $this->upgradeLogs ?: new ArrayCollection();
    }

    /**
     * UpgradeLogs.
     *
     * @param Collection $upgradeLog
     * @return Module
     */
    public function setUpgradeLogs(Collection $upgradeLogs): Module
    {
        $this->upgradeLogs = $upgradeLogs;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getEvents(): Collection
    {
        return $this->events = $this->events ?: new ArrayCollection();
    }

    /**
     * Events.
     *
     * @param Collection $events
     * @return Module
     */
    public function setEvents(Collection $events): Module
    {
        $this->events = $events;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    /**
     * Notifications.
     *
     * @param Collection $notifications
     * @return Module
     */
    public function setNotifications(Collection $notifications): Module
    {
        $this->notifications = $notifications;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getUpgrades(): Collection
    {
        return $this->upgrades;
    }

    /**
     * Upgrades.
     *
     * @param Collection $upgrades
     * @return Module
     */
    public function setUpgrades(Collection $upgrades): Module
    {
        $this->upgrades = $upgrades;
        return $this;
    }

    /**
     * __toSting
     * @return string|null
     */
    public function __toSting(): ?string
    {
        return $this->getName();
    }
}