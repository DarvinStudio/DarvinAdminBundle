<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Slug suffix form type
 */
class SlugSuffixType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach (array(
            'slug_property',
            'route',
            'route_param_slug',
        ) as $option) {
            $view->vars[$option] = $options[$option];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'slug_property'    => 'slug',
                'route'            => 'darvin_content_content_show',
                'route_param_slug' => 'slug',
                'required'         => false,
            ))
            ->setAllowedTypes('slug_property', 'string')
            ->setAllowedTypes('route', 'string')
            ->setAllowedTypes('route_param_slug', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\TextType';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'darvin_admin_slug_suffix';
    }
}
