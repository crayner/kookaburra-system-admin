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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SystemSettings
 * @package App\Form\Modules\SystemAdmin
 */
class SystemSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('systemSettingsHeader', HeaderType::class,
                [
                    'label' => 'System Settings'
                ]
            )
            ->add('systemSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'System',
                            'name' => 'absoluteURL',
                            'entry_type' => UrlType::class,
                            'entry_options' => [
                                'attr' => [
                                    'maxLength' => 100,
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'absolutePath',
                            'entry_options' => [
                                'attr' => [
                                    'maxLength' => 100,
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'systemName',
                            'entry_options' => [
                                'attr' => [
                                    'maxLength' => 50,
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'indexText',
                            'entry_type' => TextareaType::class,
                            'entry_options' => [
                                'attr' => [
                                    'rows' => 8,
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'installType',
                            'entry_type' => ChoiceType::class,
                            'entry_options' => [
                                'choices' => [
                                    'Production' => "Production",
                                    'Testing' =>  "Testing",
                                    'Development' =>  "Development",
                                ],
                                'placeholder' => false,
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