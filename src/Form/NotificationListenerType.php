<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 10/09/2019
 * Time: 14:45
 */

namespace Kookaburra\SystemAdmin\Form;

use Kookaburra\SystemAdmin\Entity\Action;
use Kookaburra\SystemAdmin\Entity\NotificationEvent;
use Kookaburra\UserAdmin\Entity\Person;
use App\Form\EventSubscriber\NotificationListenerSubscriber;
use App\Form\Type\DisplayType;
use App\Form\Type\HiddenEntityType;
use App\Provider\ProviderFactory;
use Kookaburra\SystemAdmin\Entity\NotificationListener;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class NotificationListenerType
 * @package App\Form\Modules\SystemAdmin
 */
class NotificationListenerType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $action = ProviderFactory::getRepository(Action::class)->findOneByName($options['event']->getAction()->getName());
        $roles= [];
        foreach($action->getPermissions() as $permission)
            $roles[] = $permission->getRole()->getId();

        $result = ProviderFactory::getRepository(Person::class)->findByRoles($roles);
        $people = [];
        foreach($result as $person) {
            $people[$person['name']][] = $person[0];
        }

        $allScopes = [
            'All'                   => 'All',
            'gibbonPersonIDStudent' => 'Student',
            'gibbonPersonIDStaff'   => 'Staff',
            'gibbonYearGroupID'     => 'Year Group',
        ];
        $eventScopes = array_flip($options['event']->getScopes());
        $availableScopes = array_intersect_key($allScopes, $eventScopes);

        $builder
            ->add('person', EntityType::class,
                [
                    'class' => Person::class,
                    'choice_label' => 'fullName',
                    'label' => 'Name',
                    'help' => 'Available only to users with the required permission.',
                    'choices' => $people,
                    'placeholder' => 'Please Select...',
                ]
            )
            ->add('scopeType', ChoiceType::class,
                [
                    'label' => 'Scope',
                    'placeholder' => 'Please select...',
                    'choices' => array_flip($availableScopes),
                    'on_change' => 'toggleScopeType',
                ]
            )
            ->add('scopeID', DisplayType::class,
                [
                    'label' => 'Scope Type Choices',
                ]
            )
            ->add('event', HiddenEntityType::class,
                [
                    'class' => NotificationEvent::class,
                    'row_style' => 'hidden',
                ]
            )
        ;
        $builder->addEventSubscriber(new NotificationListenerSubscriber());
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            [
                'event',
            ]
        );
        $resolver->setDefaults(
            [
                'constraints' => [
                    new \Kookaburra\SystemAdmin\Validator\NotificationListener(),
                ],
                'allow_extra_fields' => true,
                'data_class' => NotificationListener::class,
                'error_bubbling' => false,
            ]
        );
    }
}