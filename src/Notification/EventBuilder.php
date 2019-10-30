<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 25/10/2019
 * Time: 08:53
 */

namespace Kookaburra\SystemAdmin\Notification;

use App\Entity\Person;
use App\Provider\ProviderFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Kookaburra\SystemAdmin\Entity\NotificationEvent;
use Kookaburra\SystemAdmin\Entity\Module;
use Symfony\Component\OptionsResolver\Exception\OptionDefinitionException;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class EventBuilder
 * @package Kookaburra\SystemAdmin\Notification
 */
class EventBuilder
{
    /**
     * @var Module
     */
    private $module;

    /**
     * @var NotificationEvent
     */
    private $event;

    /**
     * @var string
     */
    private $text;

    /**
     * @var array
     */
    private $text_params = [];

    /**
     * @var string
     */
    private $actionLink;

    /**
     * @var ArrayCollection
     */
    private $scopes;

    /**
     * @var ArrayCollection
     */
    private $recipients;

    /**
     * @var string
     */
    private $translationDomain = 'messages';

    /**
     * @var array
     */
    private $configuration = [];

    /**
     * EventBuilder constructor.
     * @param string $module
     * @param string $event
     */
    public function __construct(string $module, string $event)
    {
        $this->module = ProviderFactory::getRepository(Module::class)->findOneByName($module);
        $this->event = ProviderFactory::getRepository(NotificationEvent::class)->findOneBy(['module' => $this->module, 'event' => $event]);

        if (null === $this->event)
            throw new OptionDefinitionException(sprintf('The event "%s" was not found in the notification system for module "%s".', $event, $module));

        $this->scopes = new ArrayCollection();
        $this->recipients = new ArrayCollection();
    }

    /**
     * @return Module
     */
    public function getModule(): Module
    {
        return $this->module;
    }

    /**
     * Module.
     *
     * @param Module $module
     * @return EventBuilder
     */
    public function setModule(Module $module): EventBuilder
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @return NotificationEvent
     */
    public function getEvent(): NotificationEvent
    {
        return $this->event;
    }

    /**
     * Event.
     *
     * @param NotificationEvent $event
     * @return EventBuilder
     */
    public function setEvent(NotificationEvent $event): EventBuilder
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Text.
     *
     * @param string $text
     * @return EventBuilder
     */
    public function setText(string $text): EventBuilder
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return array
     */
    public function getTextParams(): array
    {
        return $this->text_params ?: [];
    }

    /**
     * TextParams.
     *
     * @param array $text_params
     * @return EventBuilder
     */
    public function setTextParams(array $text_params): EventBuilder
    {
        $this->text_params = $text_params;
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
     * ActionLink.
     *
     * @param string|null $actionLink
     * @return EventBuilder
     */
    public function setActionLink(?string $actionLink): EventBuilder
    {
        $this->actionLink = $actionLink;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getScopes(): ArrayCollection
    {
        return $this->scopes = $this->scopes ?: new ArrayCollection();
    }

    /**
     * addScope
     * @param string $type
     * @param int|array $identifiers
     * @return EventBuilder
     */
    public function addScope(string $type, $identifiers): EventBuilder
    {
        if (empty($type) || empty($identifiers)) return $this;

        if (! in_array($type, ['All', 'gibbonPersonIDStaff', 'gibbonYearGroupID', 'gibbonPersonIDStudent', 'yearGroup', 'student', 'all', 'staff'])) {
            throw new OptionDefinitionException("The type must be one of ['All', 'gibbonPersonIDStaff', 'gibbonYearGroupID', 'gibbonPersonIDStudent', 'yearGroup', 'student', 'all', 'staff']");
            return $this;
        }

        if (!is_array($identifiers))
            $identifiers = [$identifiers];

        foreach ($identifiers as $identifier)
            $this->getScopes()->set($type.intval($identifier), ['type' => $type, 'id' => intval($identifier)]);

        return $this;
    }

    /**
     * Scopes.
     *
     * @param ArrayCollection $scopes
     * @return EventBuilder
     */
    public function setScopes(?ArrayCollection $scopes): EventBuilder
    {
        $this->scopes = $scopes ?: new ArrayCollection();
        return $this;
    }

    /**
     * addRecipient
     * @param Person $person
     * @return EventBuilder
     */
    public function addRecipient(Person $person): EventBuilder
    {
        if ($this->getRecipients()->contains($person))
            return $this;

        $this->recipients->add($person);

        return $this;
    }

    /**
     * getRecipients
     * @return ArrayCollection
     */
    public function getRecipients(): ArrayCollection
    {
        return $this->recipients = $this->recipients ?: new ArrayCollection();
    }

    /**
     * Recipients.
     *
     * @param ArrayCollection|null $recipients
     * @return EventBuilder
     */
    public function setRecipients(?ArrayCollection $recipients): EventBuilder
    {
        $this->recipients = $recipients;
        return $this;
    }

    /**
     * queueNotifications
     * @param string $translationDomain
     */
    public function queueNotifications(string $translationDomain = 'messages'): EventBuilder
    {
        $this->setTranslationDomain($translationDomain);
        EventBuilderProvider::addEvent($this);
        return $this;
    }

    /**
     * @return string
     */
    public function getTranslationDomain(): string
    {
        return $this->translationDomain ?: 'messages';
    }

    /**
     * TranslationDomain.
     *
     * @param string $translationDomain
     * @return EventBuilder
     */
    public function setTranslationDomain(string $translationDomain): EventBuilder
    {
        $this->translationDomain = $translationDomain ?: 'messages';
        return $this;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->configuration = $this->configuration ?: [];
    }

    /**
     * Configuration.
     *
     * @param array $configuration
     * @return EventBuilder
     */
    public function setConfiguration(array $configuration): EventBuilder
    {
        $this->configuration = $configuration ?: [];
        return $this;
    }

    /**
     * getConfig
     * @param string $name
     * @return mixed
     */
    public function getOption(string $name) {
        if (!isset($this->getConfiguration()[$name]))
            throw new MissingOptionsException(sprintf('You must set the option "%s" before you can call it!', $name), []);
        return $this->getConfiguration()[$name];
    }

    /**
     * setOption
     * @param string $name
     * @param $value
     * @return EventBuilder
     */
    public function setOption(string $name, $value): EventBuilder
    {
        $options = $this->getConfiguration();
        $options[$name] = $value;
        return $this->setConfiguration($options);
    }
}