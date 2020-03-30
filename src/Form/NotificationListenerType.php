<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 10/09/2019
 * Time: 14:45
 */

namespace Kookaburra\SystemAdmin\Form;

use App\Form\Transform\EntityToStringTransformer;
use Kookaburra\SchoolAdmin\Entity\YearGroup;
use Kookaburra\SystemAdmin\Entity\Action;
use Kookaburra\SystemAdmin\Entity\NotificationEvent;
use Kookaburra\SystemAdmin\Form\EventListener\NotificationEventSubscriber;
use Kookaburra\SystemAdmin\Form\Transform\NotificationListenerStatusIDTransform;
use Kookaburra\SystemAdmin\Form\Transform\NotificationListenerTransform;
use Kookaburra\UserAdmin\Entity\Person;
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
        $event = $options['event'];
        $action = ProviderFactory::getRepository(Action::class)->findOneByName($event->getAction()->getName());
        $roles= [];
        foreach($action->getPermissions() as $permission)
            $roles[] = $permission->getRole()->getId();

        $result = ProviderFactory::getRepository(Person::class)->findByRoles($roles);
        $people = [];
        foreach($result as $person) {
            $people[$person['name']][] = $person[0];
        }

        $allScopes = NotificationListener::getScopeTypeList();
        $eventScopes = array_flip($event->getScopes());
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
                    'chained_child' => 'scopeID',
                    'chained_values' => NotificationListener::getChainedValues(array_flip($availableScopes)),
                ]
            )
            ->add('scopeID', ChoiceType::class,
                [
                    'label' => 'Scope Choices',
                    'placeholder' => ' ',
                    'choices' => NotificationListener::getChainedValues([]),
                    'required' => false,
                ]
            )
            ->add('event', HiddenEntityType::class,
                [
                    'class' => NotificationEvent::class,
                ]
            )
        ;
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
                'data_class' => NotificationListener::class,
                'translation_domain' => 'SystemAdmin',
            ]
        );
    }
}