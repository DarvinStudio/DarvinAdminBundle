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

use Darvin\AdminBundle\Form\FormException;
use Darvin\Utils\Sluggable\SluggableManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * Slug suffix form type
 */
class SlugSuffixType extends AbstractType
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Darvin\Utils\Sluggable\SluggableManagerInterface
     */
    private $sluggableManager;

    /**
     * @param \Symfony\Component\Routing\RouterInterface        $router           Router
     * @param \Darvin\Utils\Sluggable\SluggableManagerInterface $sluggableManager Sluggable manager
     */
    public function __construct(RouterInterface $router, SluggableManagerInterface $sluggableManager)
    {
        $this->router = $router;
        $this->sluggableManager = $sluggableManager;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $route = $this->router->getRouteCollection()->get($options['route']);

        if (empty($route)) {
            throw new FormException(
                sprintf('Unable to finish slug suffix form view: route "%s" does not exist.', $options['route'])
            );
        }

        $view->vars = array_merge($view->vars, [
            'route_path'  => $route->getPath(),
            'slug_prefix' => $this->sluggableManager->getSlugPrefix($form->getParent()->getData(), $options['slug_property']),
        ]);

        foreach ([
            'slug_property',
            'route',
            'route_param_slug',
            'parent_select_selector',
            'parent_option_data_slug',
        ] as $option) {
            $view->vars[$option] = $options[$option];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'slug_property'           => 'slug',
                'route'                   => 'darvin_content_content_show',
                'route_param_slug'        => 'slug',
                'parent_select_selector'  => '.parent',
                'parent_option_data_slug' => 'slug',
                'required'                => false,
            ])
            ->setAllowedTypes('slug_property', 'string')
            ->setAllowedTypes('route', 'string')
            ->setAllowedTypes('route_param_slug', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'darvin_admin_slug_suffix';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\TextType';
    }
}
