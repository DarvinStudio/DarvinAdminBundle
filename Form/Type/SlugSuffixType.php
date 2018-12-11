<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

use Darvin\AdminBundle\Form\FormException;
use Darvin\Utils\Homepage\HomepageRouterInterface;
use Darvin\Utils\Routing\RouteManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Slug suffix form type
 */
class SlugSuffixType extends AbstractType
{
    /**
     * @var \Darvin\Utils\Homepage\HomepageRouterInterface
     */
    private $homepageRouter;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Darvin\Utils\Routing\RouteManagerInterface
     */
    private $routeManager;

    /**
     * @param \Darvin\Utils\Homepage\HomepageRouterInterface              $homepageRouter   Homepage router
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     * @param \Darvin\Utils\Routing\RouteManagerInterface                 $routeManager     Route manager
     */
    public function __construct(
        HomepageRouterInterface $homepageRouter,
        PropertyAccessorInterface $propertyAccessor,
        RouteManagerInterface $routeManager
    ) {
        $this->homepageRouter = $homepageRouter;
        $this->propertyAccessor = $propertyAccessor;
        $this->routeManager = $routeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $isHomepage = $this->homepageRouter->isHomepage($form->getParent()->getData());

        $routePath = $slug = $slugPrefix = null;

        if (!$isHomepage) {
            if (!$this->routeManager->exists($options['route'])) {
                throw new FormException(
                    sprintf('Unable to finish slug suffix form view: route "%s" does not exist.', $options['route'])
                );
            }

            $routePath = $this->routeManager->getPath($options['route']);

            $slug = $this->propertyAccessor->getValue($form->getParent()->getData(), $options['slug_property']);
            $slugSuffix = $form->getData();
            $slugPrefix = !empty($slug) && !empty($slugSuffix)
                ? preg_replace(sprintf('/%s$/', $slugSuffix), '', $slug)
                : null;
        }

        $view->vars = array_merge($view->vars, [
            'is_homepage' => $isHomepage,
            'route_path'  => $routePath,
            'slug'        => $slug,
            'slug_prefix' => $slugPrefix,
        ]);

        foreach ([
            'slug_property',
            'route',
            'route_param_slug',
            'parent_select_selector',
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
                'slug_property'          => 'slug',
                'route'                  => 'darvin_content_show',
                'route_param_slug'       => 'slug',
                'parent_select_selector' => '.parent',
                'required'               => false,
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
        return TextType::class;
    }
}
