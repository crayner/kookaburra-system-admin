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
 * Date: 3/09/2019
 * Time: 14:33
 */

namespace Kookaburra\SystemAdmin\Form;

use Kookaburra\UserAdmin\Entity\Person;
use Kookaburra\SystemAdmin\Entity\Setting;
use App\Form\Transform\EntityToStringTransformer;
use App\Form\Type\FilePathType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFileType;
use App\Form\Type\ReactFormType;
use Kookaburra\SystemAdmin\Form\SettingsType;
use App\Provider\ProviderFactory;
use App\Validator\AlwaysInValid;
use App\Validator\ReactImage;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

/**
 * Class OrganisationSettingsType
 * @package App\Form\Modules\SystemAdmin
 */
class OrganisationSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('organisationSettingsHeader', HeaderType::class,
                [
                    'label' => 'Organisation Settings'
                ]
            )
            ->add('organisationSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'System',
                            'name' => 'organisationName',
                            'entry_options' => [
                                'attr' => [
                                    'maxLength' => 50,
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationNameShort',
                            'entry_options' => [
                                'attr' => [
                                    'maxLength' => 10,
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationEmail',
                            'entry_type' => EmailType::class,
                            'entry_options' => [
                                'attr' => [
                                    'maxLength' => 75,
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationLogo',
                            'entry_type' => FilePathType::class,
                            'entry_options' => [
                                'file_prefix' => 'org_logo',
                                'empty_data' => ProviderFactory::create(Setting::class)->getSettingByScopeAsString('System','organisationLogo'),
                                'constraints' => [
                                    new ReactImage(['minWidth' => 400, 'maxWidth' => 400, 'minHeight' => 100, 'maxHeight' => 100]),
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationBackground',
                            'entry_type' => ReactFileType::class,
                            'entry_options' => [
                                'file_prefix' => 'org_bg',
                                'empty_data' => ProviderFactory::create(Setting::class)->getSettingByScopeAsString('System','organisationBackground'),
                                'constraints' => [
                                    new ReactImage(['maxSize' => '750k', 'minWidth' => '1500', 'minHeight' => '1200']),
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationAdministrator',
                            'entry_type' => EntityType::class,
                            'entry_options' => [
                                'class' => Person::class,
                                'data' => ProviderFactory::create(Setting::class)->getSettingByScopeAsInteger('System', 'organisationAdministrator'),
                                'choice_label' => 'fullName',
                                'choice_translation_domain' => false,
                                'query_builder' => function(EntityRepository $er){
                                    return $er->createQueryBuilder('p')
                                        ->select(['p','s'])
                                        ->join('p.staff', 's')
                                        ->where('p.status = :full')
                                        ->andWhere('s.id IS NOT NULL')
                                        ->setParameter('full', 'Full')
                                        ->orderBy('p.surname')
                                        ->addOrderBy('p.firstName')
                                        ;
                                },
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationDBA',
                            'entry_type' => EntityType::class,
                            'entry_options' => [
                                'class' => Person::class,
                                'choice_label' => 'fullName',
                                'choice_translation_domain' => false,
                                'query_builder' => function(EntityRepository $er){
                                    return $er->createQueryBuilder('p')
                                        ->select(['p','s'])
                                        ->join('p.staff', 's')
                                        ->where('p.status = :full')
                                        ->andWhere('s.id IS NOT NULL')
                                        ->setParameter('full', 'Full')
                                        ->orderBy('p.surname')
                                        ->addOrderBy('p.firstName')
                                        ;
                                },
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationAdmissions',
                            'entry_type' => EntityType::class,
                            'entry_options' => [
                                'class' => Person::class,
                                'choice_label' => 'fullName',
                                'choice_translation_domain' => false,
                                'query_builder' => function(EntityRepository $er){
                                    return $er->createQueryBuilder('p')
                                        ->select(['p','s'])
                                        ->join('p.staff', 's')
                                        ->where('p.status = :full')
                                        ->andWhere('s.id IS NOT NULL')
                                        ->setParameter('full', 'Full')
                                        ->orderBy('p.surname')
                                        ->addOrderBy('p.firstName')
                                        ;
                                },
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'organisationHR',
                            'entry_type' => EntityType::class,
                            'entry_options' => [
                                'class' => Person::class,
                                'choice_label' => 'fullName',
                                'choice_translation_domain' => false,
                                'query_builder' => function(EntityRepository $er){
                                    return $er->createQueryBuilder('p')
                                        ->select(['p','s'])
                                        ->join('p.staff', 's')
                                        ->where('p.status = :full')
                                        ->andWhere('s.id IS NOT NULL')
                                        ->setParameter('full', 'Full')
                                        ->orderBy('p.surname')
                                        ->addOrderBy('p.firstName')
                                        ;
                                },
                            ],
                        ],
                    ],
                ]
            )
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'SystemAdmin',
                'data_class' => null,
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
}