<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 10/09/2019
 * Time: 13:34
 */

namespace Kookaburra\SystemAdmin\Form;

use Kookaburra\SystemAdmin\Entity\NotificationEvent;
use Kookaburra\UserAdmin\Entity\Person;
use App\Entity\YearGroup;
use App\Form\Type\DisplayType;
use App\Form\Type\HeaderType;
use App\Form\Type\ParagraphType;
use App\Form\Type\ReactCollectionType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Provider\ProviderFactory;
use App\Util\ReactFormHelper;
use App\Util\TranslationsHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class NotificationEventType
 * @package App\Form\Modules\SystemAdmin
 */
class NotificationEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('header1', HeaderType::class,
                [
                    'label' => 'Edit Notification Event',
                ]
            )
            ->add('notEvent', DisplayType::class,
                [
                    'data' => TranslationsHelper::translate($options['data']->getModule()->getName()) . ': ' .  TranslationsHelper::translate($options['data']->getEvent()),
                    'label' => 'Event',
                    'mapped' => false,
                ]
            )
            ->add('notActionName', DisplayType::class,
                [
                    'label' => 'Permission Required',
                    'mapped' => false,
                    'data' => $options['data']->getAction()->getName(),
                ]
            )
            ->add('active', ToggleType::class,
                [
                    'label' => 'Active',
                ]
            )
            ->add('header2', HeaderType::class,
                [
                    'label' => 'Edit Subscribers',
                ]
            )
            ->add('id', HiddenType::class)
        ;

        if ($options['data']->getActive() === 'N')
            $builder->add('paragraph1', ParagraphType::class, [
                'help' => 'This notification event is not active. The following subscribers will not receive any notifications until the event is set to active.',
                'wrapper_class' => 'warning',
            ]);
        if ($options['data']->getType() === 'CLI')
            $builder->add('paragraph2', ParagraphType::class, [
                'help' => 'This is a CLI notification event. It will only run if the corresponding CLI script has been setup on the server.',
                'wrapper_class' => 'info',
            ]);
        $builder
            ->add('listeners', ReactCollectionType::class,
                [
                    'entry_type' => NotificationListenerType::class,
                    'entry_options' => [
                        'event' => $options['data'],
                    ],
                    'label' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'header_row' => true,
                    'row_style' => 'single',
                    'element_delete_route' => $options['listener_delete_route'],
                    'element_delete_options' => ['__id__' => 'id', '__event__' => 'event'],
                ]
            )
            ->add('submit', SubmitType::class)
        ;
        ReactFormHelper::setExtras(
            [
                'students' =>  ProviderFactory::create(Person::class)->getCurrentStudentChoiceList(),
                'staff' => ProviderFactory::create(Person::class)->getCurrentStaffChoiceList(),
                'yearGroups' => ProviderFactory::create(YearGroup::class)->getCurrentYearGroupChoiceList(),
            ]
        );

    }

    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => NotificationEvent::class,
            ]
        );
        $resolver->setRequired(
            [
                'listener_delete_route',
            ]
        );
    }
}