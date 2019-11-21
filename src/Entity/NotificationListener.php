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

use Kookaburra\UserAdmin\Entity\Person;
use App\Manager\EntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class NotificationListener
 * @package Kookaburra\SystemAdmin\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\SystemAdmin\Repository\NotificationListenerRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="NotificationListener")
 * */
class NotificationListener
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", name="gibbonNotificationListenerID", columnDefinition="INT(10) UNSIGNED ZEROFILL")
     * @ORM\GeneratedValue
     */
    private $id;
    
    /**
     * @var NotificationEvent|null
     * @ORM\ManyToOne(targetEntity="NotificationEvent", inversedBy="listeners")
     * @ORM\JoinColumn(name="gibbonNotificationEventID", referencedColumnName="gibbonNotificationEventID", nullable=true)
     */
    private $event;
    
    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\UserAdmin\Entity\Person")
     * @ORM\JoinColumn(name="gibbonPersonID", referencedColumnName="gibbonPersonID", nullable=true)
     * @ORM\OrderBy({"surname": "ASC", "firstName": "ASC"})
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=30, name="scopeType", nullable=true)
     */
    private $scopeType;

    /**
     * @var integer|null
     * @ORM\Column(type="bigint", name="scopeID", columnDefinition="INT(20) UNSIGNED", nullable=true)
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
    public function setEvent(?NotificationEvent $event): NotificationListener
    {
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
        return $this;
    }

    /**
     * @return int|null
     */
    public function getScopeID(): ?int
    {
        return $this->scopeID;
    }

    /**
     * @param int|null $scopeID
     * @return NotificationListener
     */
    public function setScopeID($scopeID): NotificationListener
    {
        if ($scopeID instanceof EntityInterface)
            $scopeID = $scopeID->getId();
        $this->scopeID = $scopeID;
        return $this;
    }
}