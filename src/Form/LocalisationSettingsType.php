<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 3/09/2019
 * Time: 14:33
 */

namespace Kookaburra\SystemAdmin\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Form\Type\SettingsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LocalisationSettingsType
 * @package App\Form\Modules\SystemAdmin
 */
class LocalisationSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('localisationSettingsHeader', HeaderType::class,
                [
                    'label' => 'Localisation'
                ]
            )
            ->add('localisationSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'System',
                            'name' => 'country',
                            'entry_type' => CountryType::class,
                            'entry_options' => [
                                'placeholder' => ' ',
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'firstDayOfTheWeek',
                            'entry_type' => ChoiceType::class,
                            'entry_options' => [
                                'choices' => [
                                    'Monday' => "Monday",
                                    'Sunday' => "Sunday",

                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'timezone',
                            'entry_type' => TimezoneType::class,
                            'entry_options' => [
                                'placeholder' => ' ',
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'currency',
                            'entry_type' => CurrencyType::class,
                            'entry_options' => [
                                'placeholder' => ' ',
                            ],
                        ],
                    ],
                ]
            )
            ->add('submit', SubmitType::class);
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'messages',
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