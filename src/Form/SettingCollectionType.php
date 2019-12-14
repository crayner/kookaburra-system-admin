<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 13/12/2019
 * Time: 11:46
 */

namespace Kookaburra\SystemAdmin\Form;

use App\Form\Type\ReactCollectionType;
use App\Util\StringHelper;
use App\Util\TranslationsHelper;
use Kookaburra\SystemAdmin\Form\EventListener\SettingCollectionListener;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Class SettingCollectionType
 * @package Kookaburra\SystemAdmin\Form
 */
class SettingCollectionType extends ReactCollectionType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new SettingCollectionListener($options));
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            [
                'collection_keys',
            ]
        );

        $resolver->setDefaults(
            [
                'element_id_name' => 'id',
                'element_delete_options' => ['__id__' => 'id'],
                'header_row' => false,
                'entry_type' => TextType::class,
                'prototype' => false,
                'element_delete_route' => false,
                'entry_options' => [],
                'compound' => true,
                'row_style' => 'transparent',
            ]
        );

        $resolver->setAllowedTypes('collection_keys', 'array');
        $resolver->setAllowedTypes('header_row', ['boolean', 'array']);
    }

    /**
     * finishView
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach($options['collection_keys'] as $key) {
            $name = StringHelper::toCamelCase($key);
            $vars['children'][$name] = $this->buildTemplateView($key,$view->children[$name]);
        }
    }

    /**
     * buildTemplateView
     * @param string $key
     * @param FormView $view
     * @return array
     */
    private function buildTemplateView(string $key, FormView $view): array
    {
        $vars = [];
        $vars['label'] = TranslationsHelper::translate($key);
        $view->vars['label'] = $vars['label'];
        return $vars;
    }
}