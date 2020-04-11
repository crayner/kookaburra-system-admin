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
 * Class Notification
 * @package Kookaburra\SystemAdmin\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\SystemAdmin\Repository\NotificationRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="Notification")
 * @ORM\HasLifecycleCallbacks()
 * */
class Notification implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(10) UNSIGNED")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\UserAdmin\Entity\Person")
     * @ORM\JoinColumn(name="person", referencedColumnName="id", nullable=false)
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=8, options={"default": "New"})
     */
    private $status = 'New';

    /**
     * @var array
     */
    private static $statusList = ['New', 'Archived'];

    /**
     * @var Module|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\SystemAdmin\Entity\Module", inversedBy="notifications")
     * @ORM\JoinColumn(name="module", referencedColumnName="id", nullable=true)
     */
    private $module;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint", columnDefinition="INT(4)", options={"default": "1"})
     */
    private $count = 1;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @var array|null
     */
    private $textOptions;

    /**
     * @var string|null
     * @ORM\Column(name="actionLink", options={"comment": "Relative to absoluteURL, start with a forward slash"},nullable=true)
     */
    private $actionLink;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Notification
     */
    public function setId(?int $id): Notification
    {
        $this->id = $id;
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
     * @return Notification
     */
    public function setPerson(?Person $person): Notification
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     * @return Notification
     */
    public function setStatus(?string $status): Notification
    {
        $this->status = in_array($status, self::getStatusList()) ? $status: 'New' ;
        return $this;
    }

    /**
     * @return Module|null
     */
    public function getModule(): ?Module
    {
        return $this->module;
    }

    /**
     * @param Module|null $module
     * @return Notification
     */
    public function setModule(?Module $module): Notification
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * @param int|null $count
     * @return Notification
     */
    public function setCount(?int $count): Notification
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string|null $text
     * @return Notification
     */
    public function setText(?string $text): Notification
    {
        $this->text = $text;
        return $this;
    }

    /**
     * getTextOptions
     * @return array|null
     */
    public function getTextOptions(): ?array
    {
        return $this->textOptions = $this->textOptions ?: [];
    }

    /**
     * setTextOptions
     * @param array|null $textOptions
     * @return Notification
     */
    public function setTextOptions(?array $textOptions): Notification
    {
        $this->textOptions = $textOptions ?: [];
        return $this;
    }

    /**
     * @return string|null
     */
    public function getActionLink(): ?string
    {
        return $this->actionLink;
    }

    /**
     * @param string|null $actionLink
     * @return Notification
     */
    public function setActionLink(?string $actionLink): Notification
    {
        $this->actionLink = $actionLink;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getTimestamp(): ?\DateTime
    {
        return $this->timestamp;
    }

    /**
     * @param \DateTime|null $timestamp
     * @return Notification
     */
    public function setTimestamp(?\DateTime $timestamp): Notification
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @return array
     */
    public static function getStatusList(): array
    {
        return self::$statusList;
    }

    /**
     * createTimestamp
     * @ORM\PrePersist()
     */
    public function createTimestamp()
    {
        if (null === $this->getTimestamp())
            $this->timestamp = new \DateTime();
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [
            'module' => $this->getModule()->getName(),
            'name' => $this->get
        ];
    }
}