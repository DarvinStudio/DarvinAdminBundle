<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form\Type;

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
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Darvin\Utils\Routing\RouteManagerInterface
     */
    private $routeManager;

    /**
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     * @param \Darvin\Utils\Routing\RouteManagerInterface                 $routeManager     Route manager
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor, RouteManagerInterface $routeManager)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->routeManager = $routeManager;
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if (!$this->routeManager->exists($options['route'])) {
            throw new \InvalidArgumentException(
                sprintf('Unable to finish slug suffix form view: route "%s" does not exist.', $options['route'])
            );
        }

        $routePath = $this->routeManager->getPath($options['route']);
        $slug      = $this->propertyAccessor->getValue($form->getParent()->getData(), $options['slug_property']);

        $slugSuffix = $form->getData();
        $slugPrefix = !empty($slug) && !empty($slugSuffix)
            ? preg_replace(sprintf('/%s$/', $slugSuffix), '', $slug)
            : null;

        $view->vars = array_merge($view->vars, [
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
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'help'                   => 'form.slug_suffix.help',
                'slug_property'          => 'slug',
                'route'                  => 'darvin_content_show',
                'route_param_slug'       => 'slug',
                'parent_select_selector' => '.js-parent',
                'required'               => false,
            ])
            ->setAllowedTypes('slug_property', 'string')
            ->setAllowedTypes('route', 'string')
            ->setAllowedTypes('route_param_slug', 'string');
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return 'darvin_admin_slug_suffix';
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): string
    {
        return TextType::class;
    }
}
