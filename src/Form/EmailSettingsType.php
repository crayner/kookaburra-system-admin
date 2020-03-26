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

use App\Entity\Setting;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFormType;
use App\Form\Type\SettingsType;
use App\Provider\ProviderFactory;
use App\Util\ReactFormHelper;
use App\Util\TranslationsHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SMSSettingsType
 * @package App\Form\Modules\SystemAdmin
 */
class EmailSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('emailHeader', HeaderType::class,
                [
                    'label' => 'E-Mail',
                ]
            )
            ->add('emailSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'System',
                            'name' => 'enableMailerSMTP',
                            'entry_type' => ChoiceType::class,
                            'entry_options' => [
                                'choices' => [
                                    'No' => 'No',
                                    'GMail' => 'GMail',
                                    'SMTP' => 'SMTP',
                                ],
                                'choice_translation_domain' => false,
                                'on_change' => 'toggleMailerRows',
                            ],
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'mailerSMTPUsername',
                            'entry_type' => TextType::class,
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'mailerSMTPPassword',
                            'entry_type' => TextType::class,
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'mailerSMTPHost',
                            'entry_type' => TextType::class,
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'mailerSMTPPort',
                            'entry_type' => IntegerType::class,
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'mailerSMTPSecure',
                            'entry_type' => ChoiceType::class,
                            'entry_options' => [
                                'choices' => [
                                    'Automatic' => 'auto',
                                    'tls'  => 'tls',
                                    'ssl'  => 'ssl',
                                    'None' => 'none',
                                ],
                            ],
                        ],
                    ],
                ]
            )
            ->add('submit', SubmitType::class);

        $provider = ProviderFactory::create(Setting::class);
        $username = $provider->getSettingByScope('System','mailerSMTPUsername', true);
        $password = $provider->getSettingByScope('System','mailerSMTPPassword', true);
        $host = $provider->getSettingByScope('System','mailerSMTPHost', true);
        $port = $provider->getSettingByScope('System','mailerSMTPPort', true);
        $secure = $provider->getSettingByScope('System','mailerSMTPSecure', true);
        TranslationsHelper::setDomain('SystemAdmin');
        ReactFormHelper::setExtras(array_merge(ReactFormHelper::getExtras(),
            [
                'mailer' => [
                    'GMail' => [
                        'System__mailerSMTPUsername' => [
                            'label' => TranslationsHelper::translate($username->getNameDisplay()),
                            'help' => TranslationsHelper::translate($username->getDescription()),
                            'visible' => true,
                        ],
                        'System__mailerSMTPPassword' => [
                            'label' => TranslationsHelper::translate($password->getNameDisplay()),
                            'help' => TranslationsHelper::translate('When using GMail with two factor authentication, you will need to create an application password on your GMail account. See {anchor}Google Account Security: App Passwords{closeAnchor}', ['{anchor}' => '<a target="_blank" href="https://myaccount.google.com/u/0/security">', '{closeAnchor}' => '</a>']),
                            'visible' => true,
                        ],
                        'System__mailerSMTPHost' => [
                            'label' => TranslationsHelper::translate($host->getNameDisplay()),
                            'help' => TranslationsHelper::translate($host->getDescription()),
                            'visible' => false,
                        ],
                        'System__mailerSMTPPort' => [
                            'label' => TranslationsHelper::translate($port->getNameDisplay()),
                            'help' => TranslationsHelper::translate($port->getDescription()),
                            'visible' => false,
                        ],
                        'System__mailerSMTPSecure' => [
                            'label' => TranslationsHelper::translate($secure->getNameDisplay()),
                            'help' => TranslationsHelper::translate($secure->getDescription()),
                            'visible' => false,
                        ],
                    ],
                    'SMTP' => [
                        'System__mailerSMTPUsername' => [
                            'label' => TranslationsHelper::translate($username->getNameDisplay()),
                            'help' => TranslationsHelper::translate($username->getDescription()),
                            'visible' => true,
                        ],
                        'System__mailerSMTPPassword' => [
                            'label' => TranslationsHelper::translate($password->getNameDisplay()),
                            'help' => TranslationsHelper::translate($password->getDescription()),
                            'visible' => true,
                        ],
                        'System__mailerSMTPHost' => [
                            'label' => TranslationsHelper::translate($host->getNameDisplay()),
                            'help' => TranslationsHelper::translate($host->getDescription()),
                            'visible' => true,
                        ],
                        'System__mailerSMTPPort' => [
                            'label' => TranslationsHelper::translate($port->getNameDisplay()),
                            'help' => TranslationsHelper::translate($port->getDescription()),
                            'visible' => true,
                        ],
                        'System__mailerSMTPSecure' => [
                            'label' => TranslationsHelper::translate($secure->getNameDisplay()),
                            'help' => TranslationsHelper::translate($secure->getDescription()),
                            'visible' => true,
                        ],
                    ],
                    'No' => [
                        'System__mailerSMTPUsername' => [
                            'visible' => false,
                        ],
                        'System__mailerSMTPPassword' => [
                            'visible' => false,
                        ],
                        'System__mailerSMTPHost' => [
                            'label' => TranslationsHelper::translate($host->getNameDisplay()),
                            'help' => TranslationsHelper::translate($host->getDescription()),
                            'visible' => false,
                        ],
                        'System__mailerSMTPSecure' => [
                            'label' => TranslationsHelper::translate($secure->getNameDisplay()),
                            'help' => TranslationsHelper::translate($secure->getDescription()),
                            'visible' => false,
                        ],
                        'System__mailerSMTPPort' => [
                            'label' => TranslationsHelper::translate($port->getNameDisplay()),
                            'help' => TranslationsHelper::translate($port->getDescription()),
                            'visible' => false,
                        ],
                    ],
                ],
            ]
        ));
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