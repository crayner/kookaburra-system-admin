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
 * Date: 20/09/2019
 * Time: 09:37
 */

namespace Kookaburra\SystemAdmin\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

/**
 * Class ImportStep1Type
 * @package App\Form\Modules\SystemAdmin
 */
class ImportStep1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $modeChoices = [
            'Update & Insert' => 'sync',
            'Update' => 'update',
            'Insert' => 'insert',
        ];
        if (!$options['importReport']->getDetail('modes')['update']) {
            $modeChoices = [
                'Insert' => 'insert',
            ];
        }
        if (!$options['importReport']->getDetail('modes')['insert']) {
            $modeChoices = [
                'Update' => 'update',
            ];
        }

        $builder
            ->add('mode', ChoiceType::class,
                [
                    'label' => 'Mode',
                    'choices' => $modeChoices,
                ]
            )
            ->add('file', FileType::class,
                [
                    'label' => 'File',
                    'help' => 'See Notes below for specification.',
                    'constraints' => [
                        new File(['mimeTypes' => ['text/csv', 'text/xml', 'text/comma-separated-values', 'text/x-comma-separated-values', 'application/vnd.ms-excel', 'application/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'application/msexcel', 'application/x-msexcel', 'application/x-ms-excel', 'application/x-excel', 'application/x-dos_ms_excel', 'application/xls', 'application/x-xls', 'application/vnd.oasis.opendocument.spreadsheet', 'application/octet-stream','application/vnd.openxmlformats-officedocument.spreadsheetml.sheetapplication/vnd.openxmlformats-officedocument.spreadsheetml.sheet']]),
                    ],
                ]
            )
            ->add('fieldDelimiter', TextType::class,
                [
                    'label' => 'Field Delimiter',
                    'attr' => [
                        'maxLength' => 1,
                    ],
                ]
            )
            ->add('stringEnclosure', TextType::class,
                [
                    'label' => 'String Enclosure',
                    'attr' => [
                        'maxLength' => 1,
                    ],
                ]
            )
            ->add('submit', SubmitType::class,
                [
                    'label' => 'Submit',
                ]
            )
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
                'attr' => [
                    'class' => 'smallIntBorder fullWidth standardForm',
                    'autocomplete' => 'on',
                    'enctype' => 'multipart/form-data',
                    'id' => 'importStep1',
                    'novalidate' => 'novalidate',
                ],
                'translation_domain' => 'messages',
            ]
        );
        $resolver->setRequired([
            'importReport',
        ]);
    }
}