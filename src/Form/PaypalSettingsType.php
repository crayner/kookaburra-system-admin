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
use App\Form\Type\ReactFormType;
use App\Form\Type\SettingsType;
use App\Form\Type\ToggleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PaypalSettingsType
 * @package App\Form\Modules\SystemAdmin
 */
class PaypalSettingsType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('paypalHeader', HeaderType::class,
                [
                    'label' => 'PayPal Payment Gateway',
                ]
            )
            ->add('paypalSettings', SettingsType::class,
                [
                    'settings' => [
                        [
                            'scope' => 'System',
                            'name' => 'enablePayments',
                            'entry_type' => ToggleType::class,
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'paypalAPIUsername',
                            'entry_type' => TextType::class,
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'paypalAPIPassword',
                            'entry_type' => TextType::class,
                        ],
                        [
                            'scope' => 'System',
                            'name' => 'paypalAPISignature',
                            'entry_type' => TextType::class,
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