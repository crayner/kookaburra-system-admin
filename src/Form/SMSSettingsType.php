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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SMSSettingsType
 * @package App\Form\Modules\SystemAdmin
 */
class SMSSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('smsHeader', HeaderType::class,
                [
                    'label' => 'SMS Settings',
                    'help' => 'Kookaburra can use a number of different gateways to send out SMS messages. These are paid services, not affiliated with Kookaburra, and you must create your own account with them before being able to send out SMSs using the Messenger module.'
                ]
            )
            ->add('smsSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'Messenger',
                            'name' => 'smsGateway',
                            'entry_type' => ChoiceType::class,
                            'entry_options' => [
                                'choices' => [
                                    'No' => 'No', 'OneWaySMS' => 'OneWaySMS', 'Twilio' => 'Twilio', 'Nexmo' => 'Nexmo', 'Clockwork' => 'Clockwork', 'TextLocal' => 'TextLocal', 'Mail to SMS' => 'Mail to SMS'
                                ],
                                'choice_translation_domain' => false,
                                'on_change' => 'toggleSMSRows',
                            ],
                        ],
                        [
                            'scope' => 'Messenger',
                            'name' => 'smsSenderID',
                            'entry_type' => TextType::class,
                            'entry_options' => [
                                'row_style' => 'hidden',
                                'attr' => [
                                    'maxLength' => 50,
                                ],
                            ],
                        ],
                        [
                            'scope' => 'Messenger',
                            'name' => 'smsUsername',
                            'entry_type' => TextType::class,
                            'entry_options' => [
                                'row_style' => 'hidden',
                                'attr' => [
                                    'maxLength' => 50,
                                ],
                            ],
                        ],
                        [
                            'scope' => 'Messenger',
                            'name' => 'smsPassword',
                            'entry_type' => TextType::class,
                            'entry_options' => [
                                'row_style' => 'hidden',
                                'attr' => [
                                    'maxLength' => 50,
                                ],
                            ],
                        ],
                        [
                            'scope' => 'Messenger',
                            'name' => 'smsURL',
                            'entry_type' => TextType::class,
                            'entry_options' => [
                                'row_style' => 'hidden',
                            ],
                        ],
                        [
                            'scope' => 'Messenger',
                            'name' => 'smsURLCredit',
                            'entry_type' => TextType::class,
                            'entry_options' => [
                                'row_style' => 'hidden',
                            ],
                        ],
                    ],
                ]
            )
            ->add('submit', SubmitType::class);
        $provider = ProviderFactory::create(Setting::class);
        $username = $provider->getSettingByScope('messenger','smsUsername', true);
        $senderID = $provider->getSettingByScope('messenger','smsSenderID', true);
        $password = $provider->getSettingByScope('messenger','smsPassword', true);
        $url = $provider->getSettingByScope('messenger','smsURL', true);
        $urlCredit = $provider->getSettingByScope('messenger','smsURLCredit', true);
        ReactFormHelper::setExtras(
            [
                'OneWaySMS' => [
                    'Messenger__smsSenderID' => [
                        'label' => TranslationsHelper::translate($senderID->getNameDisplay()),
                        'help' => TranslationsHelper::translate($senderID->getDescription()),
                        'visible' => true,
                    ],
                    'Messenger__smsUsername' => [
                        'label' => TranslationsHelper::translate($username->getNameDisplay()),
                        'help' => TranslationsHelper::translate($username->getDescription()),
                        'visible' => true,
                    ],
                    'Messenger__smsPassword' => [
                        'label' => TranslationsHelper::translate($password->getNameDisplay()),
                        'help' => TranslationsHelper::translate($password->getDescription()),
                        'visible' => true,
                    ],
                    'Messenger__smsURL' => [
                        'label' => TranslationsHelper::translate($url->getNameDisplay()),
                        'help' => TranslationsHelper::translate($url->getDescription()),
                        'visible' => true,
                    ],
                    'Messenger__smsURLCredit' => [
                        'label' => TranslationsHelper::translate($urlCredit->getNameDisplay()),
                        'help' => TranslationsHelper::translate($urlCredit->getDescription()),
                        'visible' => true,
                    ],
                ],
                'Twilio' => [
                    'Messenger__smsSenderID' => [
                        'label' => TranslationsHelper::translate($senderID->getNameDisplay()),
                        'help' => TranslationsHelper::translate($senderID->getDescription()),
                        'visible' => true,
                    ],
                    'Messenger__smsUsername' => [
                        'label' => TranslationsHelper::translate('API Key'),
                        'visible' => true,
                    ],
                    'Messenger__smsPassword' => [
                        'label' => TranslationsHelper::translate('API Secret/Auth Token'),
                        'visible' => true,
                    ],
                    'Messenger__smsURL' => [
                        'label' => TranslationsHelper::translate($url->getNameDisplay()),
                        'visible' => false,
                    ],
                    'Messenger__smsURLCredit' => [
                        'label' => TranslationsHelper::translate($urlCredit->getNameDisplay()),
                        'visible' => false,
                    ],
                ],
                'Nexmo' => [
                    'Messenger__smsSenderID' => [
                        'label' => TranslationsHelper::translate($senderID->getNameDisplay()),
                        'help' => TranslationsHelper::translate($senderID->getDescription()),
                        'visible' => true,
                    ],
                    'Messenger__smsUsername' => [
                        'label' => TranslationsHelper::translate('API Key'),
                        'visible' => true,
                    ],
                    'Messenger__smsPassword' => [
                        'label' => TranslationsHelper::translate('API Secret/Auth Token'),
                        'visible' => true,
                    ],
                    'Messenger__smsURL' => [
                        'label' => TranslationsHelper::translate($url->getNameDisplay()),
                        'visible' => false,
                    ],
                    'Messenger__smsURLCredit' => [
                        'label' => TranslationsHelper::translate($urlCredit->getNameDisplay()),
                        'visible' => false,
                    ],
                ],
                'Clockwork' => [
                    'Messenger__smsSenderID' => [
                        'label' => TranslationsHelper::translate($senderID->getNameDisplay()),
                        'help' => TranslationsHelper::translate($senderID->getDescription()),
                        'visible' => true,
                    ],
                    'Messenger__smsUsername' => [
                        'label' => TranslationsHelper::translate('API Key'),
                        'visible' => true,
                    ],
                    'Messenger__smsPassword' => [
                        'label' => TranslationsHelper::translate('API Secret/Auth Token'),
                        'visible' => false,
                    ],
                    'Messenger__smsURL' => [
                        'label' => TranslationsHelper::translate($url->getNameDisplay()),
                        'visible' => false,
                    ],
                    'Messenger__smsURLCredit' => [
                        'label' => TranslationsHelper::translate($urlCredit->getNameDisplay()),
                        'visible' => false,
                    ],
                ],
                'TextLocal' => [
                    'Messenger__smsSenderID' => [
                        'label' => TranslationsHelper::translate($senderID->getNameDisplay()),
                        'help' => TranslationsHelper::translate($senderID->getDescription()),
                        'visible' => true,
                    ],
                    'Messenger__smsUsername' => [
                        'label' => TranslationsHelper::translate('API Key'),
                        'visible' => true,
                    ],
                    'Messenger__smsPassword' => [
                        'label' => TranslationsHelper::translate('API Secret/Auth Token'),
                        'visible' => false,
                    ],
                    'Messenger__smsURL' => [
                        'label' => TranslationsHelper::translate($url->getNameDisplay()),
                        'visible' => false,
                    ],
                    'Messenger__smsURLCredit' => [
                        'label' => TranslationsHelper::translate($urlCredit->getNameDisplay()),
                        'visible' => false,
                    ],
                ],
                'Mail to SMS' => [
                    'Messenger__smsSenderID' => [
                        'label' => TranslationsHelper::translate($senderID->getNameDisplay()),
                        'help' => TranslationsHelper::translate($senderID->getDescription()),
                        'visible' => true,
                    ],
                    'Messenger__smsUsername' => [
                        'label' => TranslationsHelper::translate('SMS Domain'),
                        'visible' => true,
                    ],
                    'Messenger__smsPassword' => [
                        'label' => TranslationsHelper::translate('API Secret/Auth Token'),
                        'visible' => false,
                    ],
                    'Messenger__smsURL' => [
                        'label' => TranslationsHelper::translate($url->getNameDisplay()),
                        'visible' => false,
                    ],
                    'Messenger__smsURLCredit' => [
                        'label' => TranslationsHelper::translate($urlCredit->getNameDisplay()),
                        'visible' => false,
                    ],
                ],
                'No' => [
                    'Messenger__smsSenderID' => [
                        'label' => false,
                        'visible' => false,
                    ],
                    'Messenger__smsUsername' => [
                        'label' => false,
                        'visible' => false,
                    ],
                    'Messenger__smsPassword' => [
                        'label' => false,
                        'visible' => false,
                    ],
                    'Messenger__smsURL' => [
                        'label' => false,
                        'visible' => false,
                    ],
                    'Messenger__smsURLCredit' => [
                        'label' => false,
                        'visible' => false,
                    ],
                ],
            ]
        );

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