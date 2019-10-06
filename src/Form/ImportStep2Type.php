<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 20/09/2019
 * Time: 09:37
 */

namespace Kookaburra\SystemAdmin\Form;

use App\Form\Entity\ImportControl;
use App\Validator\SystemAdmin\ImportStep2;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\BooleanToStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ImportStep2Type
 * @package App\Form\Modules\SystemAdmin
 */
class ImportStep2Type extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mode', HiddenType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                        new Choice(['choices' => ["sync","insert","update"]])
                    ],
                ]
            )
            ->add('fieldDelimiter', HiddenType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('stringEnclosure', HiddenType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('ignoreErrors', HiddenType::class)
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Submit',
                ]
            )
        ;
        $builder->get('ignoreErrors')->addViewTransformer(new BooleanToStringTransformer('1', [null,'0','']));
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'attr' => [
                    'class' => 'smallIntBorder fullWidth standardForm',
                    'autocomplete' => 'on',
                    'enctype' => 'multipart/form-data',
                    'id' => 'importStep2',
                    'novalidate' => 'novalidate',
                ],
                'translation_domain' => 'messages',
                'data_class' => ImportControl::class,
                'constraints' => [
                    new ImportStep2(),
                ],
            ]
        );
        $resolver->setRequired(
            [
                'importReport',
            ]
        );
    }
}