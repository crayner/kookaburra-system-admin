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

use App\Form\Type\ToggleType;
use Kookaburra\SystemAdmin\Form\Transform\ColumnsToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ImportStep3Type
 * @package App\Form\Modules\SystemAdmin
 */
class ImportStep3Type extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mode', HiddenType::class)
            ->add('fieldDelimiter', HiddenType::class)
            ->add('stringEnclosure', HiddenType::class)
            ->add('syncField', HiddenType::class)
            ->add('syncKey', HiddenType::class)
            ->add('columns', HiddenType::class)
            ->add('ignoreErrors', ToggleType::class,
                [
                    'label' => 'Ignore Errors? (ExpertOnly)',
                    'visibleByClass' => 'ignoreErrors',
                    'visibleWhen' => '1',
                    'values' => ['1', '0'],
                    'wrapper_class' => 'flex-1 relative right',
                ]
            )
            ->add('syncField', HiddenType::class)
            ->add('csvData', TextareaType::class,
                [
                    'label' => 'Data',
                    'help' => 'This value cannot be changed.',
                    'attr' => [
                        'rows' => 4,
                        'cols' => 74,
                        'readonly' => 'readonly',
                    ],
                    'required' => false,
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Submit',
                    'row_class' => 'ignoreErrors'
                ]
            )
        ;
        $builder->get('columns')->addViewTransformer(new ColumnsToStringTransformer());
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
                    'id' => 'importStep3',
                ],
                'translation_domain' => 'messages',
            ]
        );
        $resolver->setRequired(
            [
                'importReport',
            ]
        );
    }
}