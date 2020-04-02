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
 * Date: 7/09/2019
 * Time: 11:57
 */

namespace Kookaburra\SystemAdmin\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\ReactFileType;
use App\Form\Type\ReactFormType;
use Kookaburra\SystemAdmin\Form\SettingsType;
use App\Form\Type\ToggleType;
use App\Validator\ReactFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class GoogleIntegationType
 * @package App\Form\Modules\SystemAdmin
 */
class GoogleIntegationType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('googleIntegrationHeader', HeaderType::class,
                [
                    'label' => 'Google Integration',
                    'help' => 'If your school uses Google Apps, you can enable single sign on and calendar integration with Kookaburra. This process makes use of Google\'s APIs, and allows a user to access Kookaburra without a username and password, provided that their listed email address is a Google account to which they have access. For configuration instructions, {oneString}click here{twoString}.',
                    'help_translation_parameters' => [
                        '{oneString}' => "<a href='https://gibbonedu.org/support/administrators/installing-gibbon/authenticating-with-google-oauth/' target='_blank'>",
                        '{twoString}' => '</a>',
                    ],
                    'translation_domain' => 'SystemAdmin',
                ]
            )
            ->add('googleIntegrationSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'System',
                            'name' => 'googleOAuth',
                            'entry_type' => ToggleType::class,
                        ],
                    ],
                ]
            )
            ->add('clientSecretFile', ReactFileType::class,
                [
                    'constraints' => [
                        new ReactFile(['mimeTypes' => ['text/plain'], 'maxSize' => '1k']),
                    ],
                    'label' => 'Google OAuth Download File',
                    'help' => 'Provide a copy of the .json file downloaded from the %{anchor}Google Development Console.%{anchorClose}',
                    'help_translation_parameters' => [
                        '%{anchor}' => "<a href='https://console.cloud.google.com/apis/credentials' target='_blank'>",
                        '%{anchorClose}' => "</a>",
                    ],
                    'translation_domain' => 'SystemAdmin',
                    'file_prefix' => 'temp',
                    'data' => '',
                ]
            )
            ->add('googleSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'System',
                            'name' => 'googleDeveloperKey',
                            'entry_type' => TextType::class,
                            'entry_options' => [
                                'help' => 'Provide a copy of the API Key from the %{anchor}Google Development Console.%{anchorClose}',
                                'help_translation_parameters' => [
                                    '%{anchor}' => "<a href='https://console.cloud.google.com/apis/credentials' target='_blank'>",
                                    '%{anchorClose}' => "</a>",
                                ],
                                'translation_domain' => 'SystemAdmin',
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'calendarFeed',
                            'entry_type' => EmailType::class,
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