<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 22/09/2019
 * Time: 09:56
 */

namespace Kookaburra\SystemAdmin\Form;

use Kookaburra\SystemAdmin\Form\Entity\ImportColumn;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ImportStepColumnType
 * @package App\Form\Modules\SystemAdmin
 */
class ImportStepColumnType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('order', HiddenType::class)
            ->add('text', HiddenType::class)
            ->add('name', HiddenType::class)
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
                'data_class' => ImportColumn::class,
            ]
        );
    }
}