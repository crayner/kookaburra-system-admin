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

use App\Provider\ProviderFactory;
use Kookaburra\SchoolAdmin\Entity\YearGroup;
use Kookaburra\SystemAdmin\Validator as Valid;
use Kookaburra\UserAdmin\Entity\Person;
use App\Manager\EntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class NotificationListener
 * @package Kookaburra\SystemAdmin\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\SystemAdmin\Repository\NotificationListenerRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="NotificationListener")
 * @Valid\EventListener()
 */
class NotificationListener implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(10) UNSIGNED ZEROFILL")
     * @ORM\GeneratedValue
     */
    private $id;
    
    /**
     * @var NotificationEvent|null
     * @ORM\ManyToOne(targetEntity="NotificationEvent", inversedBy="listeners")
     * @ORM\JoinColumn(name="notification_event", referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $event;
    
    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\UserAdmin\Entity\Person")
     * @ORM\JoinColumn(referencedColumnName="id",name="person")
     * @ORM\OrderBy({"surname": "ASC", "firstName": "ASC"})
     * @Assert\NotBlank()
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=30, name="scopeType", nullable=true)
     * @Assert\Choice(callback="getScopeTypeList")
     * @Assert\NotBlank()
     */
    private $scopeType;

    /**
     * @var array
     */
    private static $scopeTypeList = [
        'All',
        'Student',
        'Staff',
        'Year Group'
    ];

    /**
     * @var string|null
     * @ORM\Column(length=20,name="scopeID",nullable=true)
     */
    private $scopeID;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return NotificationListener
     */
    public function setId(?int $id): NotificationListener
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return NotificationEvent|null
     */
    public function getEvent(): ?NotificationEvent
    {
        return $this->event;
    }

    /**
     * @param NotificationEvent|null $notification
     * @return NotificationListener
     */
    public function setEvent(?NotificationEvent $event, bool $mirror = true): NotificationListener
    {
        if ($mirror)
            $event->addListener($this, false);
        $this->event = $event;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * @param Person|null $person
     * @return NotificationListener
     */
    public function setPerson(?Person $person): NotificationListener
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getScopeType(): ?string
    {
        return $this->scopeType;
    }

    /**
     * @param string|null $scopeType
     * @return NotificationListener
     */
    public function setScopeType(?string $scopeType): NotificationListener
    {
        $this->scopeType = $scopeType;
        if ($scopeType === 'All')
            $this->setScopeID(null);
        return $this;
    }

    /**
     * getScopeID
     * @return string|null
     */
    public function getScopeID(): ?string
    {
        if ($this->getScopeType() === 'All')
            $this->scopeID = null;
        return $this->scopeID;
    }

    /**
     * setScopeID
     * @param string|null $scopeID
     * @return NotificationListener
     */
    public function setScopeID(?string $scopeID): NotificationListener
    {
        if ($this->getScopeType() === 'All')
            $scopeID  = null;
        $this->scopeID = $scopeID;
        return $this;
    }

    /**
     * getScopeTypeList
     * @return array
     */
    public static function getScopeTypeList(): array
    {
        $result = [];
        foreach(self::$scopeTypeList as $name)
            $result[$name] = $name;
        return $result;
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

    /**
     * getChainedValues
     * @param array $available
     * @return array
     */
    public static function getChainedValues(array $available): array
    {
        $result = [];
        if (array_key_exists('All', $available) || $available === [])
            $result['All'] = [];

        if (array_key_exists('Student', $available) || $available === [])
            $result['Student'] = ProviderFactory::create(Person::class)->getCurrentStudentChoiceList();

        if (array_key_exists('Staff', $available) || $available === [])
            $result['Staff'] = ProviderFactory::create(Person::class)->getCurrentStaffChoiceList();

        if (array_key_exists('Year Group', $available) || $available === [])
            $result['Year Group'] = ProviderFactory::create(YearGroup::class)->getCurrentYearGroupChoiceList();

        return $result;
    }
}