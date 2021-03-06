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
use App\Form\Type\FilePathType;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFileType;
use App\Form\Type\ReactFormType;
use Kookaburra\SystemAdmin\Form\SettingsType;
use App\Form\Type\ToggleType;
use App\Provider\ProviderFactory;
use Kookaburra\UserAdmin\Util\SecurityHelper;
use App\Validator\AlwaysInValid;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class SecuritySettingsType
 * @package App\Form\Modules\SystemAdmin
 */
class SecuritySettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('securitySettingsHeader', HeaderType::class,
                [
                    'label' => 'Security Settings'
                ]
            )
            ->add('passwordPolicyHeader', HeaderType::class,
                [
                    'label' => 'Password Policy',
                    'header_type' => 'h4',
                    'row_class' => 'flex flex-col sm:flex-row justify-between content-center p-0',
                ]
            )
            ->add('policySettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'System',
                            'name' => 'passwordPolicyMinLength',
                            'entry_type' => IntegerType::class,
                            'entry_options' => [
                                'constraints' => [
                                    new Range(['min' => 8, 'max' => 12]),
                                ],
                                'attr' => [
                                    'min' => 8,
                                    'max' => 12,
                                ],
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'passwordPolicyAlpha',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'passwordPolicyNumeric',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'passwordPolicyNonAlphaNumeric',
                            'entry_type' => ToggleType::class,
                        ],
                    ],
                ]
            )
            ->add('miscellaneousHeader', HeaderType::class,
                [
                    'label' => 'Miscellaneous',
                    'header_type' => 'h4',
                    'row_class' => 'flex flex-col sm:flex-row justify-between content-center p-0',
                ]
            )
            ->add('miscellaneousSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'System',
                            'name' => 'sessionDuration',
                            'entry_type' => IntegerType::class,
                            'entry_options' => [
                                'constraints' => [
                                    new Range(['min' => 900, 'max' => ini_get("session.gc_maxlifetime") < 43200000 ? ini_get("session.gc_maxlifetime") : 43200000]),
                                ],
                                'attr' => [
                                    'min' => 900,
                                    'max' => ini_get("session.gc_maxlifetime") < 43200000 ? ini_get("session.gc_maxlifetime") : 43200000,
                                ],
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