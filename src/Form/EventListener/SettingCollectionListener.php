<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 13/12/2019
 * Time: 15:40
 */

namespace Kookaburra\SystemAdmin\Form\EventListener;


use App\Util\StringHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class SettingCollectionListener
 * @package Kookaburra\SystemAdmin\Form\EventListener
 */
class SettingCollectionListener implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * SettingCollectionListener constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => ['preSetData', 128],
//            FormEvents::PRE_SUBMIT => ['preSubmit', 128],
        ];
    }

    /**
     * preSetData
     * @param PreSetDataEvent $event
     */
    public function preSetData(PreSetDataEvent $event)
    {
        $value = $event->getData();

        $value = $value ?: [];
        if (is_string($value)) {
            $value = json_decode($value, true);
            $value = new ArrayCollection(is_array($value) ? $value : []);
        }
        if (is_array($value))
            $value = new ArrayCollection($value);

        $new = new ArrayCollection();
        foreach($this->options['collection_keys'] as $key)
        {
            $name = StringHelper::toSnakeCase($key);
            if ($value->containsKey($name))
                $new->set($name, $value->get($name));
            else
                $new->set($name, '');
        }

        $event->setData($new);
    }

    /**
     * preSubmit
     * @param PreSubmitEvent $event
     */
    public function preSubmit(PreSubmitEvent $event)
    {
        $data = $event->getData();

        $data = is_array($data) ? json_encode($data) : json_encode([]);

        $event->setData($data);
    }
}